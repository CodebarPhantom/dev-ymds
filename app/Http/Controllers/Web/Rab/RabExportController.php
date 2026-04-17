<?php

namespace App\Http\Controllers\Web\Rab;

use App\Exports\RabExport;
use App\Http\Controllers\MasterController;
use App\Models\Rab;
use App\Services\Dashboard\DashboardRabService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

class RabExportController extends MasterController
{
    protected DashboardRabService $dashboardRabService;

    public function __construct(DashboardRabService $dashboardRabService)
    {
        parent::__construct();
        $this->dashboardRabService = $dashboardRabService;
    }

    public function export(Request $request)
    {
        Gate::authorize('readPolicy', Rab::class);

        $filters = [
            'tahun'     => $request->tahun,
            'bulan'     => $request->bulan,
            'divisi_id' => $request->divisi_id,
        ];

        $export   = new RabExport($filters, auth()->user());
        $filename = 'RAB_Export_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download($export, $filename);
    }
}
