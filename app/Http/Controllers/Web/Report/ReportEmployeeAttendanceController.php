<?php

namespace App\Http\Controllers\Web\Report;


use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;


class ReportEmployeeAttendanceController extends MasterController
{
    /**
     * Display a listing of the resource.
     */
    public function attendance(Request $request)
    {
        $func = function () {
            $breadcrumbs = ['Laporan', 'Karyawan', 'Kehadiran' ];
            $pageTitle = "Laporan Kehadiran";
            //auth()->user()->hasPermissionTo('report-employee-attendance');
            $this->data = compact('breadcrumbs', 'pageTitle');

        };

        return $this->callFunction($func, view('backoffice.report.employee.attendance'));
    }


}
