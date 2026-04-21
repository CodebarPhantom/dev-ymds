<?php

namespace App\Http\Controllers\Web\PurchaseRequest;

use App\Http\Controllers\MasterController;
use App\Models\PurchaseRequest;
use App\Services\Dashboard\DashboardPurchaseRequestService;
use Illuminate\Support\Facades\Gate;

class PurchaseRequestDashboardController extends MasterController
{
    protected DashboardPurchaseRequestService $dashboardService;

    public function __construct(DashboardPurchaseRequestService $dashboardService)
    {
        parent::__construct();
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', PurchaseRequest::class);

            $breadcrumbs = ['Pengajuan Pembelian Barang','Dashboard'];
            $pageTitle        = 'Dashboard Pengajuan Pembelian Barang';
            $availableYears   = $this->dashboardService->getAvailableYears();
            $divisiList       = $this->dashboardService->getDivisiList();
            $isPrivilegedUser = $this->isPrivilegedUser();

            $this->data = compact('breadcrumbs', 'pageTitle', 'availableYears', 'divisiList', 'isPrivilegedUser');
        };

        return $this->callFunction($func, view('purchase-request.dashboard'));
    }

    private function isPrivilegedUser(): bool
    {
        $user     = auth()->user();
        $roleName = $user->getRoleNames()->first();

        return in_array($roleName, ['bendahara_umum', 'ketua_yayasan', 'divisi_sarpras']);
    }
}
