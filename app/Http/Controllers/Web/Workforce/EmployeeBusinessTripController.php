<?php

namespace App\Http\Controllers\Web\Workforce;

use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use App\Models\Workforce\EmployeeBusinessTrip;
use Illuminate\Support\Facades\Gate;

use App\Services\Workforce\EmployeeBusinessTripService;
use App\Services\Workforce\EmployeeService;



class EmployeeBusinessTripController extends MasterController
{
    protected $employeeBusinessTripService;
    protected $employeeService;


    public function __construct(
        EmployeeBusinessTripService $employeeBusinessTripService,
        EmployeeService $employeeService
    ) {
        parent::__construct();
        $this->employeeBusinessTripService = $employeeBusinessTripService;
        $this->employeeService = $employeeService;

    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', EmployeeBusinessTrip::class);

            $breadcrumbs = ['Tenaga Kerja', 'Formulir', 'Perjalanan Dinas'];
            $pageTitle = "Perjalanan Dinas";
            $flashMessageSuccess = session('flashMessageSuccess');
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess');
        };

        return $this->callFunction($func, view('backoffice.workforce.form.business-trip.index'));
    }

    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', EmployeeBusinessTrip::class);
            $breadcrumbs = ['Tenaga Kerja', 'Perjalanan Dinas', 'Tambah Perjalanan Dinas'];
            $pageTitle = "Tambah Perjalanan Dinas";
            $employees = $this->employeeService->getAllEmployees();

            $this->data = compact('breadcrumbs', 'pageTitle', 'employees');
        };

        return $this->callFunction($func, view('backoffice.workforce.form.business-trip.create'));


    }

    public function store(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('createPolicy', EmployeeBusinessTrip::class);

            $data = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'note' => 'nullable|string',
                'employee_id' => 'integer'
            ]);

            $this->employeeBusinessTripService->createBusinessTripForEmployee($data);
            $this->messages = ['Perjalanan Dinas berhasil dibuat'];
            session()->flash('flashMessageSuccess');
        };

        return $this->callFunction($func, null, 'workforce.submitted-form.business-trip.index');

    }

    public function show($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', EmployeeBusinessTrip::class);
            $breadcrumbs = ['Tenaga Kerja', 'Perjalanan Dinas', 'Lihat Perjalanan Dinas'];
            $pageTitle = "Lihat Perjalanan Dinas";
            $businessTrip = $this->employeeBusinessTripService->showBusinessTrip($id);

            $this->data = compact('breadcrumbs', 'pageTitle','businessTrip');
        };

        return $this->callFunction($func, view('backoffice.workforce.form.business-trip.show'));
    }

    public function edit($id)
    {
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', EmployeeBusinessTrip::class);

            $breadcrumbs = ['Tenaga Kerja', 'Perjalanan Dinas', 'Ubah Perjalanan Dinas'];
            $pageTitle = "Ubah Perjalanan Dinas";
            $businessTrip = $this->employeeBusinessTripService->showBusinessTrip($id);
            $employees = $this->employeeService->getAllEmployees();


            $this->data = compact('breadcrumbs', 'pageTitle','businessTrip','employees');

        };
        return $this->callFunction($func, view('backoffice.workforce.form.business-trip.edit'));
    }

    public function update($id, Request $request)
    {
        $func = function () use ($id, $request) {
            Gate::authorize('updatePolicy', EmployeeBusinessTrip::class);

            $data = $request->validate([
                'type' => 'string'
            ]);

            $this->employeeBusinessTripService->updateBusinessTrip($id, $data);
            $this->messages = ['Perjalanan Dinas berhasil diubah'];
            session()->flash('flashMessageSuccess');


        };
        return $this->callFunction($func, null, 'workforce.submitted-form.business-trip.index');
    }

    public function confirmBusinessTripUpdate($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', EmployeeBusinessTrip::class);
            $this->employeeBusinessTripService->approveBusinessTrip($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');

        };
        return $this->callFunction($func, null, 'workforce.submitted-form.business-trip.index');
    }


    public function rejectBusinessTripUpdate($id)
    {
        $func = function () use ($id) {

            $this->employeeBusinessTripService->rejectBusinessTrip($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');

        };
        return $this->callFunction($func, null, 'workforce.submitted-form.business-trip.index');
    }
}
