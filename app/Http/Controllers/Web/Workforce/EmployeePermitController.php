<?php

namespace App\Http\Controllers\Web\Workforce;

use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use App\Models\Workforce\EmployeePermit;
use Illuminate\Support\Facades\Gate;

use App\Services\Workforce\EmployeePermitService;
use App\Services\Workforce\EmployeeService;



class EmployeePermitController extends MasterController
{
    protected $employeePermitService;
    protected $employeeService;


    public function __construct(
        EmployeePermitService $employeePermitService,
        EmployeeService $employeeService
    ) {
        parent::__construct();
        $this->employeePermitService = $employeePermitService;
        $this->employeeService = $employeeService;

    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', EmployeePermit::class);

            $breadcrumbs = ['Tenaga Kerja', 'Formulir', 'Izin'];
            $pageTitle = "Izin";
            $flashMessageSuccess = session('flashMessageSuccess');
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess');
        };

        return $this->callFunction($func, view('backoffice.workforce.form.permit.index'));
    }

    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', EmployeePermit::class);
            $breadcrumbs = ['Tenaga Kerja', 'Izin', 'Tambah Izin'];
            $pageTitle = "Tambah Izin";
            $employees = $this->employeeService->getAllEmployees();

            $this->data = compact('breadcrumbs', 'pageTitle', 'employees');
        };

        return $this->callFunction($func, view('backoffice.workforce.form.permit.create'));


    }

    public function store(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('createPolicy', EmployeePermit::class);

            $data = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'note' => 'nullable|string',
                'employee_id' => 'integer'
            ]);

            $this->employeePermitService->createPermitForEmployee($data);
            $this->messages = ['Izin berhasil dibuat'];
            session()->flash('flashMessageSuccess');
        };

        return $this->callFunction($func, null, 'workforce.submitted-form.permit.index');

    }

    public function show($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', EmployeePermit::class);
            $breadcrumbs = ['Tenaga Kerja', 'Izin', 'Lihat Izin'];
            $pageTitle = "Lihat Izin";
            $permit = $this->employeePermitService->showPermit($id);

            $this->data = compact('breadcrumbs', 'pageTitle','permit');
        };

        return $this->callFunction($func, view('backoffice.workforce.form.permit.show'));
    }

    public function edit($id)
    {
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', EmployeePermit::class);

            $breadcrumbs = ['Tenaga Kerja', 'Izin', 'Ubah Izin'];
            $pageTitle = "Ubah Izin";
            $permit = $this->employeePermitService->showPermit($id);

            $this->data = compact('breadcrumbs', 'pageTitle','permit');

        };
        return $this->callFunction($func, view('backoffice.workforce.form.permit.edit'));
    }

    public function update($id, Request $request)
    {
        $func = function () use ($id, $request) {
            Gate::authorize('updatePolicy', EmployeePermit::class);

            $data = $request->validate([
                'type' => 'string'
            ]);

            $this->employeePermitService->updatePermit($id, $data);
            $this->messages = ['Izin berhasil diubah'];
            session()->flash('flashMessageSuccess');


        };
        return $this->callFunction($func, null, 'workforce.submitted-form.permit.index');
    }

    public function confirmPermitUpdate($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', EmployeePermit::class);
            $this->employeePermitService->approvePermit($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');

        };
        return $this->callFunction($func, null, 'workforce.submitted-form.permit.index');
    }

    public function confirmPermitUpdateUnpaidLeave($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', EmployeePermit::class);
            $this->employeePermitService->approvePermitUnpaidLeave($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };
        return $this->callFunction($func, null, 'workforce.submitted-form.permit.index');
    }

    public function rejectPermitUpdate($id)
    {
        $func = function () use ($id) {

            $this->employeePermitService->rejectPermit($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');

        };
        return $this->callFunction($func, null, 'workforce.submitted-form.permit.index');
    }
}
