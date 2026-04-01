<?php

namespace App\Http\Controllers\Web\Workforce;

use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use App\Models\Workforce\EmployeeOvertime;
use Illuminate\Support\Facades\Gate;
use App\Services\Workforce\EmployeeOvertimeService;
use App\Services\Workforce\EmployeeService;
use App\Services\WorkSchedule\ShiftService;




class EmployeeOvertimeController extends MasterController
{
    protected $employeeOvertimeService;
    protected $employeeService;
    protected $shiftService;


    public function __construct(
        EmployeeOvertimeService $employeeOvertimeService,
        EmployeeService $employeeService,
        ShiftService $shiftService

    ) {
        parent::__construct();
        $this->employeeOvertimeService = $employeeOvertimeService;
        $this->employeeService = $employeeService;
        $this->shiftService = $shiftService;

    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', EmployeeOvertime::class);

            $breadcrumbs = ['Tenaga Kerja', 'Formulir', 'Lembur'];
            $pageTitle = "Lembur";
            $flashMessageSuccess = session('flashMessageSuccess');
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess');
        };

        return $this->callFunction($func, view('backoffice.workforce.form.overtime.index'));
    }

    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', EmployeeOvertime::class);
            $breadcrumbs = ['Tenaga Kerja', 'Lembur', 'Tambah Lembur'];
            $pageTitle = "Tambah Lembur";
            $employees = $this->employeeService->getAllEmployees();
            $shifts = $this->shiftService->getAllShift();

            $this->data = compact('breadcrumbs', 'pageTitle', 'employees', 'shifts');
        };

        return $this->callFunction($func, view('backoffice.workforce.form.overtime.create'));
    }

    public function store(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('createPolicy', EmployeeOvertime::class);

            $data = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'note' => 'nullable|string',
                'employee_id' => 'required|integer',
                'overtime_pay' => 'required|integer',
                'overtime_hour'=> 'required|integer',
                'shift_id' => 'required|integer'
            ]);

            $this->employeeOvertimeService->createOvertimeForEmployee($data);
            $this->messages = ['Lembur berhasil dibuat'];
            session()->flash('flashMessageSuccess');
        };

        return $this->callFunction($func, null, 'workforce.submitted-form.overtime.index');
    }

    public function show($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', EmployeeOvertime::class);
            $breadcrumbs = ['Tenaga Kerja', 'Lembur', 'Lihat Lembur'];
            $pageTitle = "Lihat Lembur";
            $editPageTitle = "Hitung Lembur";

            $overtime = $this->employeeOvertimeService->showOvertime($id);

            $this->data = compact('breadcrumbs', 'pageTitle', 'overtime', 'editPageTitle');
        };

        return $this->callFunction($func, view('backoffice.workforce.form.overtime.show'));
    }

    public function edit($id)
    {
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', EmployeeOvertime::class);

            $breadcrumbs = ['Tenaga Kerja', 'Lembur', 'Perhitungan Lembur'];
            $pageTitle = "Setujui Perhitungan Lembur";
            $overtime = $this->employeeOvertimeService->showOvertime($id);

            $this->data = compact('breadcrumbs', 'pageTitle', 'overtime');
        };
        return $this->callFunction($func, view('backoffice.workforce.form.overtime.edit'));
    }

    public function update($id, Request $request)
    {
        $func = function () use ($id, $request) {
            Gate::authorize('updatePolicy', EmployeeOvertime::class);

            $data = $request->validate([
                'overtime_pay' => 'integer'
            ]);

            //$this->employeeOvertimeService->updateOvertime($id, $data);
            $this->employeeOvertimeService->approveOvertime($id,$data);

            $this->messages = ['Lembur berhasil dihitung'];
            session()->flash('flashMessageSuccess');
        };
        return $this->callFunction($func, null, 'workforce.submitted-form.overtime.index');
    }

    // public function confirmOvertimeUpdate($id)
    // {
    //     $func = function () use ($id) {
    //         Gate::authorize('readPolicy', EmployeeOvertime::class);
    //         $this->employeeOvertimeService->approveOvertime($id);
    //         session()->flash('flashMessageSuccess', 'Task was successful!');
    //     };
    //     return $this->callFunction($func, null, 'workforce.submitted-form.overtime.index');
    // }

    public function rejectOvertimeUpdate($id)
    {
        $func = function () use ($id) {

            $this->employeeOvertimeService->rejectOvertime($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };
        return $this->callFunction($func, null, 'workforce.submitted-form.overtime.index');
    }
}
