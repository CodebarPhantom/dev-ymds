<?php

namespace App\Http\Controllers\Api\V1\Rab;

use App\Http\Controllers\MasterController;
use App\Models\Rab;
use App\Services\RabService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RabApprovalController extends MasterController
{
    protected RabService $rabService;

    public function __construct(RabService $rabService)
    {
        parent::__construct();
        $this->rabService = $rabService;
    }

    public function approve(Request $request, Rab $rab)
    {
        $func = function () use ($request, $rab) {
            Gate::authorize('approvePolicy', $rab);
            $this->rabService->approveRab($rab, auth()->user(), $request->input('catatan'));
            $this->messages = ['RAB berhasil disetujui.'];
        };

        return $this->callFunction($func);
    }

    public function reject(Request $request, Rab $rab)
    {
        $func = function () use ($request, $rab) {
            Gate::authorize('approvePolicy', $rab);
            $request->validate(['catatan' => 'required|string']);
            $this->rabService->rejectRab($rab, auth()->user(), $request->input('catatan'));
            $this->messages = ['RAB berhasil ditolak.'];
        };

        return $this->callFunction($func);
    }
}
