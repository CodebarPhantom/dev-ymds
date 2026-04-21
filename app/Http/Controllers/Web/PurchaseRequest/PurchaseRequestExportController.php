<?php

namespace App\Http\Controllers\Web\PurchaseRequest;

use App\Exports\PurchaseRequestExport;
use App\Http\Controllers\MasterController;
use App\Models\PurchaseRequest;
use App\Services\PurchaseRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseRequestExportController extends MasterController
{
    protected PurchaseRequestService $purchaseRequestService;

    public function __construct(PurchaseRequestService $purchaseRequestService)
    {
        parent::__construct();
        $this->purchaseRequestService = $purchaseRequestService;
    }

    public function export(Request $request)
    {
        Gate::authorize('readPolicy', PurchaseRequest::class);

        $filters = [
            'tahun'     => $request->tahun,
            'bulan'     => $request->bulan,
            'divisi_id' => $request->divisi_id,
        ];

        /** @var \App\Models\User $user */
        $user  = auth()->user();
        $query = $this->purchaseRequestService->getDatatableQuery($filters, $user);

        if (!empty($filters['tahun'])) {
            $query->whereYear('tanggal_dibutuhkan', $filters['tahun']);
        }

        if (!empty($filters['bulan'])) {
            $query->whereMonth('tanggal_dibutuhkan', $filters['bulan']);
        }

        $filename = 'PurchaseRequest_Export_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new PurchaseRequestExport($query), $filename);
    }
}
