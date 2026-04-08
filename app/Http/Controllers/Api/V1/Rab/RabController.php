<?php

namespace App\Http\Controllers\Api\V1\Rab;

use App\Http\Controllers\MasterController;
use App\Models\Rab;
use App\Services\RabService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RabController extends MasterController
{
    protected RabService $rabService;

    public function __construct(RabService $rabService)
    {
        parent::__construct();
        $this->rabService = $rabService;
    }

    public function dataTable(Request $request)
    {
        Gate::authorize('readPolicy', Rab::class);

        $page      = (int) $request->input('page', 1);
        $size      = (int) $request->input('size', 10);
        $search    = $request->input('search', '');
        $sortField = $request->input('sortField', 'created_at');
        $sortOrder = $request->input('sortOrder', 'desc');

        $filters = [
            'search'    => $search,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'status'    => $request->input('status'),
            'divisi_id' => $request->input('divisi_id'),
            'bulan'     => $request->input('bulan'),
        ];

        $query     = $this->rabService->getDatatableQuery($filters, auth()->user());
        $paginated = $query->paginate($size, ['*'], 'page', $page);

        Carbon::setLocale('id');

        $mappedData = $paginated->map(function (Rab $rab) {
            return [
                'id'                             => $rab->id,
                'nomor_rab'                      => $rab->nomor_rab,
                'divisi_id'                      => $rab->divisi_id,
                'bulan_pengajuan'                => $rab->bulan_pengajuan->format('Y-m-d'),
                'bulan_pengajuan_label'          => $rab->bulan_pengajuan->translatedFormat('F Y'),
                'total_kegiatan'                 => $rab->total_kegiatan,
                'total_biaya_anggaran'           => (float) $rab->total_biaya_anggaran,
                'total_biaya_anggaran_formatted' => number_format($rab->total_biaya_anggaran, 0, ',', '.'),
                'status'                         => $rab->status->value,
                'status_label'                   => $rab->status_label,
                'status_color'                   => $rab->status_color,
                'can_edit'                       => $rab->isEditable() && Gate::check('updatePolicy', $rab),
                'can_submit'                     => $rab->isSubmittable() && Gate::check('cancelPolicy', $rab),
                'can_cancel'                     => $rab->isCancellable() && Gate::check('cancelPolicy', $rab),
                'can_approve'                    => Gate::check('approvePolicy', $rab) && (
                    ($rab->status->value === 'PENDING_BENDAHARA' && auth()->user()->hasRole('bendahara_umum')) ||
                    ($rab->status->value === 'PENDING_KETUA' && auth()->user()->hasRole('ketua_yayasan'))
                ),
            ];
        });

        return response()->json([
            'page'       => $page,
            'pageCount'  => $paginated->lastPage(),
            'sortField'  => $sortField,
            'sortOrder'  => $sortOrder,
            'totalCount' => $paginated->total(),
            'data'       => $mappedData->values(),
        ]);
    }

    public function submit(Rab $rab)
    {
        $func = function () use ($rab) {
            Gate::authorize('cancelPolicy', $rab);
            $this->rabService->submitRab($rab, auth()->user());
            $this->messages = ['RAB berhasil diajukan.'];
        };

        return $this->callFunction($func);
    }

    public function cancel(Rab $rab)
    {
        $func = function () use ($rab) {
            Gate::authorize('cancelPolicy', $rab);
            $this->rabService->cancelRab($rab, auth()->user());
            $this->messages = ['RAB berhasil dibatalkan.'];
        };

        return $this->callFunction($func);
    }
}
