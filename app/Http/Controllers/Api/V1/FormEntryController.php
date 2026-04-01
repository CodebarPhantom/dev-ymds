<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\MasterController;
use App\Models\Surah;
use Illuminate\Http\Request;
use App\Services\FormService;
use App\Services\SurahService;
use App\Services\UserService;
use Illuminate\Support\Facades\Log;

class FormEntryController extends MasterController
{
    protected $formService, $surahService, $userService;


    // Inject multiple services through the constructor
    public function __construct(FormService $formService, SurahService $surahService, UserService $userService)
    {
        parent::__construct();
        $this->formService = $formService;
        $this->surahService = $surahService;
        $this->userService = $userService;
    }

    public function store(Request $request)
    {
        $func = function () use ($request) {

            Log::info('Form data received: ', $request->all());
            $this->messages = ['Tahsin Tilawah berhasil ditambahkan!'];
        };

        return $this->callFunction($func);

    }
}
