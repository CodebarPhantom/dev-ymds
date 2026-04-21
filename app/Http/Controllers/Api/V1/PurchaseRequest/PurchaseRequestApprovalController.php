<?php

namespace App\Http\Controllers\Api\V1\PurchaseRequest;

use App\Exceptions\CustomException;
use App\Http\Controllers\MasterController;
use App\Http\Requests\PurchaseRequest\RejectPurchaseRequestRequest;
use App\Models\PurchaseRequest;
use App\Services\PurchaseRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PurchaseRequestApprovalController extends MasterController
{
    protected PurchaseRequestService $purchaseRequestService;

    public function __construct(PurchaseRequestService $purchaseRequestService)
    {
        parent::__construct();
        $this->purchaseRequestService = $purchaseRequestService;
    }

    public function submit(PurchaseRequest $purchaseRequest)
    {
        $func = function () use ($purchaseRequest) {
            $user = auth()->user();

            if ($user->getRoleNames()->first() !== $purchaseRequest->divisi_id) {
                throw new CustomException('Anda tidak berwenang untuk mengajukan pengajuan ini.', 403);
            }

            $pr = $this->purchaseRequestService->submit($purchaseRequest, $user);

            $this->data     = ['id' => $pr->id, 'status' => $pr->status->value];
            $this->messages = ['Pengajuan berhasil diajukan.'];
        };

        return $this->callFunction($func);
    }

    public function cancel(PurchaseRequest $purchaseRequest)
    {
        $func = function () use ($purchaseRequest) {
            Gate::authorize('cancelPolicy', $purchaseRequest);

            $pr = $this->purchaseRequestService->cancel($purchaseRequest, auth()->user());

            $this->data     = ['id' => $pr->id, 'status' => $pr->status->value];
            $this->messages = ['Pengajuan berhasil dibatalkan.'];
        };

        return $this->callFunction($func);
    }

    public function approve(Request $request, PurchaseRequest $purchaseRequest)
    {
        $func = function () use ($request, $purchaseRequest) {
            Gate::authorize('approvePolicy', $purchaseRequest);

            $pr = $this->purchaseRequestService->approve(
                $purchaseRequest,
                auth()->user(),
                $request->input('catatan')
            );

            $this->data     = ['id' => $pr->id, 'status' => $pr->status->value];
            $this->messages = ['Pengajuan berhasil disetujui.'];
        };

        return $this->callFunction($func);
    }

    public function reject(RejectPurchaseRequestRequest $request, PurchaseRequest $purchaseRequest)
    {
        $func = function () use ($request, $purchaseRequest) {
            Gate::authorize('approvePolicy', $purchaseRequest);

            $pr = $this->purchaseRequestService->reject(
                $purchaseRequest,
                auth()->user(),
                $request->input('catatan')
            );

            $this->data     = ['id' => $pr->id, 'status' => $pr->status->value];
            $this->messages = ['Pengajuan berhasil ditolak.'];
        };

        return $this->callFunction($func);
    }
}
