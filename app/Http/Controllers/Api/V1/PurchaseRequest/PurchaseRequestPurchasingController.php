<?php

namespace App\Http\Controllers\Api\V1\PurchaseRequest;

use App\Http\Controllers\MasterController;
use App\Http\Requests\PurchaseRequest\MarkItemPurchasedRequest;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Services\PurchaseRequestService;
use Illuminate\Support\Facades\Gate;

class PurchaseRequestPurchasingController extends MasterController
{
    protected PurchaseRequestService $purchaseRequestService;

    public function __construct(PurchaseRequestService $purchaseRequestService)
    {
        parent::__construct();
        $this->purchaseRequestService = $purchaseRequestService;
    }

    public function startPurchasing(PurchaseRequest $purchaseRequest)
    {
        $func = function () use ($purchaseRequest) {
            Gate::authorize('purchasingPolicy', $purchaseRequest);

            $pr = $this->purchaseRequestService->startPurchasing($purchaseRequest, auth()->user());

            $this->data     = ['id' => $pr->id, 'status' => $pr->status->value];
            $this->messages = ['Proses pembelian berhasil dimulai.'];
        };

        return $this->callFunction($func);
    }

    public function markItemPurchased(MarkItemPurchasedRequest $request, PurchaseRequest $purchaseRequest, PurchaseRequestItem $item)
    {
        $func = function () use ($request, $purchaseRequest, $item) {
            Gate::authorize('purchasingPolicy', $purchaseRequest);

            $pr = $this->purchaseRequestService->markItemPurchased(
                $purchaseRequest,
                $item,
                $request->validated(),
                auth()->user()
            );

            $this->data     = ['id' => $pr->id, 'status' => $pr->status->value];
            $this->messages = ['Item berhasil ditandai sudah dibeli.'];
        };

        return $this->callFunction($func);
    }
}
