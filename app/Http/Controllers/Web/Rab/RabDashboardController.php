<?php

namespace App\Http\Controllers\Web\Rab;

use App\Http\Controllers\MasterController;
use App\Models\Rab;
use App\Services\Dashboard\DashboardRabService;
use Illuminate\Support\Facades\Gate;

class RabDashboardController extends MasterController
{
    protected DashboardRabService $dashboardRabService;

    public function __construct(DashboardRabService $dashboardRabService)
    {
        parent::__construct();
        $this->dashboardRabService = $dashboardRabService;
    }

    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', Rab::class);

            $availableYears = $this->dashboardRabService->getAvailableYears();
            $divisiList     = $this->dashboardRabService->getDivisiList();

            $breadcrumbs = ['RAB', 'Dashboard'];
            $pageTitle   = 'Dashboard RAB';
            $this->data  = compact('breadcrumbs', 'pageTitle', 'availableYears', 'divisiList');
        };

        return $this->callFunction($func, view('rab.dashboard'));
    }
}
