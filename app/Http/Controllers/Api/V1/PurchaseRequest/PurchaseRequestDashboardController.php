<?php

namespace App\Http\Controllers\Api\V1\PurchaseRequest;

use App\Http\Controllers\MasterController;
use App\Models\PurchaseRequest;
use App\Services\Dashboard\DashboardPurchaseRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PurchaseRequestDashboardController extends MasterController
{
    protected DashboardPurchaseRequestService $dashboardService;

    public function __construct(DashboardPurchaseRequestService $dashboardService)
    {
        parent::__construct();
        $this->dashboardService = $dashboardService;
    }

    public function summary(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('readPolicy', PurchaseRequest::class);

            validator($request->all(), [
                'tahun'     => 'required|integer|min:2000|max:2100',
                'bulan'     => 'nullable|integer|between:1,12',
                'divisi_id' => 'nullable|string',
            ])->validate();

            $filters = [
                'tahun'     => $request->tahun,
                'bulan'     => $request->bulan,
                'divisi_id' => $request->divisi_id,
            ];

            $this->data = $this->dashboardService->getSummary($filters, auth()->user());
        };

        return $this->callFunction($func);
    }

    public function chart(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('readPolicy', PurchaseRequest::class);

            validator($request->all(), [
                'tahun'     => 'required|integer|min:2000|max:2100',
                'bulan'     => 'nullable|integer|between:1,12',
                'divisi_id' => 'nullable|string',
            ])->validate();

            $filters = [
                'tahun'     => $request->tahun,
                'bulan'     => $request->bulan,
                'divisi_id' => $request->divisi_id,
            ];

            $this->data = $this->dashboardService->getChartData($filters, auth()->user());
        };

        return $this->callFunction($func);
    }
}
