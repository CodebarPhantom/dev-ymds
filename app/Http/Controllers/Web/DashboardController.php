<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\MasterController;
use App\Services\FormService;
use App\Services\SurahService;
use App\Services\UserService;
use App\Services\EntryHeaderService;
use App\Services\EntryDetailService;


class DashboardController extends MasterController
{

    protected $formService, $surahService, $userService, $entryHeaderService, $entryDetailService;


    // Inject multiple services through the constructor
    public function __construct(FormService $formService, SurahService $surahService, UserService $userService, EntryHeaderService $entryHeaderService, EntryDetailService $entryDetailService)
    {
        parent::__construct();
        $this->formService = $formService;
        $this->surahService = $surahService;
        $this->userService = $userService;
        $this->entryHeaderService = $entryHeaderService;
        $this->entryDetailService = $entryDetailService;
    }

    public function index()
    {
        $func = function ()  {
            //Gate::authorize('indexPolicy', User::class); // is from policy
            $breadcrumbs = ['Beranda'];
            $pageTitle = "Beranda Saya";
            $forms = $this->formService->getAllForms();
            $this->data = compact('breadcrumbs','pageTitle','forms');
        };

        return $this->callFunction($func, view('dashboard.index'));

    }
}
