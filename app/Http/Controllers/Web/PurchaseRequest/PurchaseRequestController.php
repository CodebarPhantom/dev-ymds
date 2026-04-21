<?php

namespace App\Http\Controllers\Web\PurchaseRequest;

use App\Http\Controllers\MasterController;
use App\Http\Requests\PurchaseRequest\StorePurchaseRequestRequest;
use App\Http\Requests\PurchaseRequest\UpdatePurchaseRequestRequest;
use App\Models\PurchaseRequest;
use App\Services\PurchaseRequestService;
use Illuminate\Support\Facades\Gate;

class PurchaseRequestController extends MasterController
{
    protected PurchaseRequestService $purchaseRequestService;

    public function __construct(PurchaseRequestService $purchaseRequestService)
    {
        parent::__construct();
        $this->purchaseRequestService = $purchaseRequestService;
    }

    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', PurchaseRequest::class);

            $breadcrumbs = [
                ['title' => 'Pengajuan Pembelian Barang', 'url' => route('purchase-requests.index')],
                ['title' => 'Daftar Pengajuan'],
            ];
            $pageTitle = 'Daftar Pengajuan Pembelian Barang';
            $isPrivilegedUser = $this->isPrivilegedUser();

            $this->data = compact('breadcrumbs', 'pageTitle', 'isPrivilegedUser');
        };

        return $this->callFunction($func, view('purchase-request.index'));
    }

    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', PurchaseRequest::class);

            $breadcrumbs = [
                ['title' => 'Pengajuan Pembelian Barang', 'url' => route('purchase-requests.index')],
                ['title' => 'Buat Pengajuan'],
            ];
            $pageTitle = 'Buat Pengajuan Pembelian Barang';

            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('purchase-request.create'));
    }

    public function store(StorePurchaseRequestRequest $request)
    {
        $func = function () use ($request) {
            Gate::authorize('createPolicy', PurchaseRequest::class);

            $this->purchaseRequestService->store($request->validated(), auth()->user());
            $this->messages = ['Pengajuan berhasil dibuat.'];
        };

        return $this->callFunction($func, null, 'purchase-requests.index');
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $func = function () use ($purchaseRequest) {
            Gate::authorize('readPolicy', PurchaseRequest::class);

            $user = auth()->user();
            $roleName = $user->getRoleNames()->first();

            if ($roleName && str_starts_with($roleName, 'divisi_') && !$this->isPrivilegedUser() && $purchaseRequest->divisi_id !== $roleName) {
                abort(403);
            }

            $purchaseRequest->load(['items', 'logs.dilakukanOleh']);

            $breadcrumbs = [
                ['title' => 'Pengajuan Pembelian Barang', 'url' => route('purchase-requests.index')],
                ['title' => 'Detail Pengajuan'],
            ];
            $pageTitle = 'Detail Pengajuan Pembelian Barang';

            $this->data = compact('breadcrumbs', 'pageTitle', 'purchaseRequest');
        };

        return $this->callFunction($func, view('purchase-request.show'));
    }

    public function edit(PurchaseRequest $purchaseRequest)
    {
        $func = function () use ($purchaseRequest) {
            Gate::authorize('updatePolicy', $purchaseRequest);

            $purchaseRequest->load('items');

            $breadcrumbs = [
                ['title' => 'Pengajuan Pembelian Barang', 'url' => route('purchase-requests.index')],
                ['title' => 'Edit Pengajuan'],
            ];
            $pageTitle = 'Edit Pengajuan Pembelian Barang';

            $this->data = compact('breadcrumbs', 'pageTitle', 'purchaseRequest');
        };

        return $this->callFunction($func, view('purchase-request.edit'));
    }

    public function update(UpdatePurchaseRequestRequest $request, PurchaseRequest $purchaseRequest)
    {
        $func = function () use ($request, $purchaseRequest) {
            Gate::authorize('updatePolicy', $purchaseRequest);

            $this->purchaseRequestService->update($purchaseRequest, $request->validated());
            $this->messages = ['Pengajuan berhasil diperbarui.'];
        };

        return $this->callFunction($func, null, 'purchase-requests.index');
    }

    private function isPrivilegedUser(): bool
    {
        $user = auth()->user();
        $roleName = $user->getRoleNames()->first();

        return in_array($roleName, ['bendahara_umum', 'ketua_yayasan', 'divisi_sarpras']);
    }
}
