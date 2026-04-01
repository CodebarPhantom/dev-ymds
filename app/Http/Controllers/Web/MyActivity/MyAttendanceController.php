<?php

namespace App\Http\Controllers\Web\MyActivity;

use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use App\Services\WorkSchedule\ShiftAttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\CompanyService;

class MyAttendanceController extends MasterController
{
    protected $shiftAttendanceService;
    protected $dateNow;
    protected $employeeId;
    protected $companyService;

    public function __construct(
        ShiftAttendanceService $shiftAttendanceService,
        CompanyService $companyService
    ) {
        parent::__construct();
        $this->shiftAttendanceService = $shiftAttendanceService;
        $this->dateNow = Carbon::now()->format('Y-m-d');
        $this->employeeId =Auth::user()->employee_id;
        $this->companyService = $companyService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $func = function () {
            $breadcrumbs = ['Aktifitas Saya', 'Kehadiran'];
            $pageTitle = "Kehadiran";
            $flashMessageSuccess = session('flashMessageSuccess');
            $shiftAttendance = $this->shiftAttendanceService->getShiftAttendanceByDateEmployeeId(
                $this->dateNow,
                $this->employeeId
            );
            $companies = $this->companyService->getAllCompany();
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess','shiftAttendance','companies');
        };

        return $this->callFunction($func, view('backoffice.my-activity.attendance.index'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
