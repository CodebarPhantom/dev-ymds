<?php

namespace App\Http\Controllers\Api\V1\PurchaseRequest;

use App\Http\Controllers\MasterController;
use App\Models\PurchaseRequest;
use App\Services\PurchaseRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PurchaseRequestController extends MasterController
{
    protected PurchaseRequestService $purchaseRequestService;

    public function __construct(PurchaseRequestService $purchaseRequestService)
    {
        parent::__construct();
        $this->purchaseRequestService = $purchaseRequestService;
    }

    public function dataTable(Request $request)
    {
        Gate::authorize('readPolicy', PurchaseRequest::class);

        $page      = (int) $request->input('page', 1);
        $size      = (int) $request->input('size', 10);
        $sortField = $request->input('sortField', 'created_at');
        $sortOrder = $request->input('sortOrder', 'desc');

        $filters = [
            'search'             => $request->input('search', ''),
            'sortField'          => $sortField,
            'sortOrder'          => $sortOrder,
            'status'             => $request->input('status'),
            'divisi_id'          => $request->input('divisi_id'),
            'tanggal_dibutuhkan' => $request->input('tanggal_dibutuhkan'),
        ];

        $user  = auth()->user();
        $query = $this->purchaseRequestService->getDatatableQuery($filters, $user);

        $paginated = $query->paginate($size, ['*'], 'page', $page);

        $data = $paginated->map(function (PurchaseRequest $pr) use ($user) {
            $canApprove = Gate::check('approvePolicy', $pr) && (
                ($pr->status->value === 'PENDING_BENDAHARA' && $user->hasRole('bendahara_umum')) ||
                ($pr->status->value === 'PENDING_KETUA' && $user->hasRole('ketua_yayasan'))
            );

            $canStartPurchasing = $user->hasRole('divisi_sarpras') && $pr->isPurchasable();

            return [
                'id'                             => $pr->id,
                'nomor_pengajuan'                => $pr->nomor_pengajuan,
                'divisi_id'                      => $pr->divisi_id,
                'judul_pengajuan'                => $pr->judul_pengajuan,
                'tanggal_dibutuhkan'             => $pr->tanggal_dibutuhkan?->format('Y-m-d'),
                'total_item'                     => $pr->total_item,
                'total_biaya_estimasi'           => $pr->total_biaya_estimasi,
                'total_biaya_estimasi_formatted' => number_format($pr->total_biaya_estimasi, 0, ',', '.'),
                'total_biaya_aktual'             => $pr->total_biaya_aktual,
                'total_biaya_aktual_formatted'   => number_format($pr->total_biaya_aktual, 0, ',', '.'),
                'status'                         => $pr->status->value,
                'status_label'                   => $pr->status_label,
                'status_color'                   => $pr->status_color,
                'can_edit'                       => $pr->isEditable() && Gate::check('updatePolicy', $pr),
                'can_submit'                     => $pr->isSubmittable() && Gate::check('cancelPolicy', $pr),
                'can_cancel'                     => $pr->isCancellable() && Gate::check('cancelPolicy', $pr),
                'can_approve'                    => $canApprove,
                'can_start_purchasing'           => $canStartPurchasing,
            ];
        });

        return response()->json([
            'page'       => $page,
            'pageCount'  => $paginated->lastPage(),
            'sortField'  => $sortField,
            'sortOrder'  => $sortOrder,
            'totalCount' => $paginated->total(),
            'data'       => $data->values(),
        ]);
    }
}
