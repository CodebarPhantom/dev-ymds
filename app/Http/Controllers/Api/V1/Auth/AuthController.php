<?php

namespace App\Http\Controllers\Api\V1\Auth;
use Illuminate\Http\Request;
use App\Services\Auth\AuthService;
use App\Http\Controllers\MasterController;

class AuthController extends MasterController
{
    protected $authService;

    // Inject multiple services through the constructor
    public function __construct(AuthService $authService)
    {
        parent::__construct();
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $func = function () use ($request) {

            $data = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
                'access_token_type' => 'required|string',
                'remember_me' => 'boolean'
            ]);

            $user = $this->authService->loginUser($data);

            // Set the data and messages
            $this->data = $user;
            $this->messages = ["Login successfully"];
        };

        return $this->callFunction($func, null, null);
    }


    /**
    * Create user
    *
    * @param  [string] name
    * @param  [string] email
    * @param  [string] password
    * @param  [string] password_confirmation
    * @return [string] message
    */
    // public function register(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string',
    //         'email'=>'required|string|unique:users',
    //         'password'=>'required|string',
    //         'c_password' => 'required|same:password'
    //     ]);

    //     $user = new User([
    //         'name'  => $request->name,
    //         'email' => $request->email,
    //         'password' => bcrypt($request->password),
    //     ]);

    //     if($user->save()){
    //         $tokenResult = $user->createToken('Personal Access Token');
    //         $token = $tokenResult->plainTextToken;

    //         return response()->json([
    //         'message' => 'Successfully created user!',
    //         'accessToken'=> $token,
    //         ],201);
    //     }
    //     else{
    //         return response()->json(['error'=>'Provide proper details']);
    //     }
    // }


}
