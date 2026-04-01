<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class MasterController extends Controller
{
    public $data;
    public $messages;
    public $error;
    public $code;

    public function __construct()
    {
        $this->data = null;
        $this->messages = [];
        $this->error = false;
        $this->code = 200;
    }

    /**
    *Calls a given function and handles exceptions and transactions.
    *@param callable $func The function to call.
    *@param string|null $view The view to render if the request is made from a web page.
    *@param string|null $redirect The URL to redirect to if the request is a redirection.
    *@return \Illuminate\Http\Response|\Illuminate\View\View|\Illuminate\Http\RedirectResponse The response object or the rendered view.
    */
    public function callFunction($func, $view = null, $redirect = null)
    {
        if ($func) {
            DB::beginTransaction();
            try {
                $func($this);
                if (!count($this->messages)) {
                    array_push($this->messages, "Success");
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                report($e);
                $this->error = true;
                $this->code = 500;

                if ($e instanceof ValidationException) {
                    $errors = $e->validator->errors()->getMessages();
                    foreach ($errors as $error) {
                        array_push($this->messages, $error[0]);
                    }
                    $this->code = $e->status;
                } elseif ($e instanceof QueryException) {
                    array_push($this->messages, $e->errorInfo[2]);
                } elseif ($e instanceof ModelNotFoundException) {
                    array_push($this->messages, $e->getMessage());
                    $this->code = 404;
                } elseif ($e instanceof CustomException) {
                    array_push($this->messages, $e->getMessage());
                    $this->code = $e->getCode();
                } else {
                    array_push($this->messages, $e->getMessage());
                }

                // If the return type is a view or a redirect, rethrow the exception to show the Laravel debug page
                if ($view || $redirect) {
                    // env('APP_ENV') == 'local' ? throw $e : abort($this->code);
                    throw $e;
                }
            }
        }

        // If a view is passed, check if it is a view object
        if ($view instanceof \Illuminate\View\View) {
            // return view($view, [
            //     "data" => $this->data,
            //     "messages" => $this->messages,
            //     "error" => $this->error,
            //     "code" => $this->code
            // ]);
            // Add additional data (if needed) to the view object
            return $view->with([
                "data" => $this->data,
                "messages" => $this->messages,
                "error" => $this->error,
                "code" => $this->code
            ]);
        } elseif ($redirect) {
            return redirect()->route($redirect)->with([
                "data" => $this->data,
                "messages" => $this->messages,
                "error" => $this->error,
                "code" => $this->code
            ]);
        } else {
            return response([
                "data" => $this->data,
                "messages" => $this->messages,
                "error" => $this->error,
            ], $this->code);
        }
    }
}
