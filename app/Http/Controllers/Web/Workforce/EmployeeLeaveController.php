<?php

namespace App\Http\Controllers\Web\Workforce;

use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use App\Models\Workforce\EmployeeLeave;
use Illuminate\Support\Facades\Gate;

use App\Services\Workforce\EmployeeLeaveService;
use App\Services\Workforce\EmployeeService;



class EmployeeLeaveController extends MasterController
{
    protected $employeeLeaveService;
    protected $employeeService;


    public function __construct(
        EmployeeLeaveService $employeeLeaveService,
        EmployeeService $employeeService
    ) {
        parent::__construct();
        $this->employeeLeaveService = $employeeLeaveService;
        $this->employeeService = $employeeService;

    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', EmployeeLeave::class);

            $breadcrumbs = ['Tenaga Kerja', 'Formulir', 'Cuti'];
            $pageTitle = "Cuti";
            $flashMessageSuccess = session('flashMessageSuccess');
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess');
        };

        return $this->callFunction($func, view('backoffice.workforce.form.leave.index'));
    }

    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', EmployeeLeave::class);
            $breadcrumbs = ['Tenaga Kerja', 'Cuti', 'Tambah Cuti'];
            $pageTitle = "Tambah Cuti";
            $employees = $this->employeeService->getAllEmployees();

            $this->data = compact('breadcrumbs', 'pageTitle', 'employees');
        };

        return $this->callFunction($func, view('backoffice.workforce.form.leave.create'));


    }

    public function store(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('createPolicy', EmployeeLeave::class);

            $data = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'note' => 'nullable|string',
                'employee_id' => 'integer',
                'type' => 'string'
            ]);

            $this->employeeLeaveService->createLeaveForEmployee($data);
            $this->messages = ['Cuti berhasil dibuat'];
            session()->flash('flashMessageSuccess');
        };

        return $this->callFunction($func, null, 'workforce.submitted-form.leave.index');

    }

    public function show($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', EmployeeLeave::class);
            $breadcrumbs = ['Tenaga Kerja', 'Cuti', 'Lihat Cuti'];
            $pageTitle = "Lihat Cuti";
            $leave = $this->employeeLeaveService->showLeave($id);

            $this->data = compact('breadcrumbs', 'pageTitle','leave');
        };

        return $this->callFunction($func, view('backoffice.workforce.form.leave.show'));
    }

    public function edit($id)
    {
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', EmployeeLeave::class);

            $breadcrumbs = ['Tenaga Kerja', 'Cuti', 'Ubah Cuti'];
            $pageTitle = "Ubah Cuti";
            $leave = $this->employeeLeaveService->showLeave($id);

            $this->data = compact('breadcrumbs', 'pageTitle','leave');

        };
        return $this->callFunction($func, view('backoffice.workforce.form.leave.edit'));
    }

    public function update($id, Request $request)
    {
        $func = function () use ($id, $request) {
            Gate::authorize('updatePolicy', EmployeeLeave::class);

            $data = $request->validate([
                'type' => 'string'
            ]);

            $this->employeeLeaveService->updateLeave($id, $data);
            $this->messages = ['Cuti berhasil diubah'];
            session()->flash('flashMessageSuccess');


        };
        return $this->callFunction($func, null, 'workforce.submitted-form.leave.index');
    }

    public function confirmLeaveUpdate($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', EmployeeLeave::class);
            $this->employeeLeaveService->approveLeave($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');

        };
        return $this->callFunction($func, null, 'workforce.submitted-form.leave.index');
    }

    public function rejectLeaveUpdate($id)
    {
        $func = function () use ($id) {

            $this->employeeLeaveService->rejectLeave($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');

        };
        return $this->callFunction($func, null, 'workforce.submitted-form.leave.index');
    }
}
