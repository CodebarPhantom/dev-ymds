<?php

namespace App\Http\Controllers\Api\V1\Rab;

use App\Http\Controllers\MasterController;
use App\Models\Rab;
use App\Services\Dashboard\DashboardRabService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RabDashboardController extends MasterController
{
    protected DashboardRabService $dashboardRabService;

    public function __construct(DashboardRabService $dashboardRabService)
    {
        parent::__construct();
        $this->dashboardRabService = $dashboardRabService;
    }

    public function summary(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('readPolicy', Rab::class);

            validator($request->all(), [
                'tahun'     => 'required|integer',
                'bulan'     => 'nullable|integer|between:1,12',
                'divisi_id' => 'nullable|string',
            ])->validate();

            $filters = [
                'tahun'     => $request->tahun,
                'bulan'     => $request->bulan,
                'divisi_id' => $request->divisi_id,
            ];

            $this->data = $this->dashboardRabService->getSummary($filters, auth()->user());
        };

        return $this->callFunction($func);
    }

    public function chart(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('readPolicy', Rab::class);

            validator($request->all(), [
                'tahun'     => 'required|integer',
                'bulan'     => 'nullable|integer|between:1,12',
                'divisi_id' => 'nullable|string',
            ])->validate();

            $filters = [
                'tahun'     => $request->tahun,
                'bulan'     => $request->bulan,
                'divisi_id' => $request->divisi_id,
            ];

            $this->data = $this->dashboardRabService->getChartData($filters, auth()->user());
        };

        return $this->callFunction($func);
    }
}
