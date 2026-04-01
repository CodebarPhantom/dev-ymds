<?php

namespace App\Http\Controllers\Web\Workforce;

use App\Http\Controllers\MasterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Workforce\EmployeePayroll;
use App\Services\Workforce\EmployeePayrollCombenService;
use App\Services\Workforce\EmployeeService;



class EmployeePayrollCombenController extends MasterController
{
    protected $employeePayrollCombenService;
    protected $employeeService;

    public function __construct(
        EmployeePayrollCombenService $employeePayrollCombenService,
        EmployeeService $employeeService
    ) {
        parent::__construct();
        $this->employeePayrollCombenService = $employeePayrollCombenService;
        $this->employeeService = $employeeService;


    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', EmployeePayroll::class);

            $breadcrumbs = ['Tenaga Kerja', 'Gaji','Compensation Benefit'];
            $pageTitle = "Compensation Benefit";
            $flashMessageSuccess = session('flashMessageSuccess');
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess');
        };

        return $this->callFunction($func, view('backoffice.workforce.employee-payroll.comben.index'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $func = function () {
            Gate::authorize('createPolicy', EmployeePayroll::class);
            $breadcrumbs = ['Tenaga Kerja', 'Gaji', 'Tambah Compensation Benefit'];
            $pageTitle = "Tambah Compensation Benefit";
            $employees = $this->employeeService->getAllEmployeeForSelect();

            $this->data = compact('breadcrumbs', 'pageTitle', 'employees');
        };

        return $this->callFunction($func, view('backoffice.workforce.employee-payroll.comben.create'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('createPolicy', EmployeePayroll::class);

            if ($request->has('value')) {
                $request->merge([
                    'value' => str_replace(',', '', $request->input('value')), // Remove commas from the single value
                ]);
            }

            $data = $request->validate([
                'employee_id' => 'required|int',
                'date' => 'required|date',
                'note' => 'required|string',
                'value' => 'required|numeric'
            ]);

            $this->employeePayrollCombenService->createComben($data);
            $this->messages = ['Compensation benefit berhasil dibuat'];
            session()->flash('flashMessageSuccess');
        };

        return $this->callFunction($func, null, 'workforce.employee-payroll-comben.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $func = function () use ($id){
            Gate::authorize('readPolicy', EmployeePayroll::class);
            $breadcrumbs = ['Tenaga Kerja', 'Gaji', 'Compensation Benefit', 'Lihat Compensation Benefit'];
            $pageTitle = "Lihat Compensation Benefit";
            $editPageTitle = "Ubah Compensation Benefit";
            $flashMessageSuccess = session('flashMessageSuccess');
            $comben = $this->employeePayrollCombenService->getCombenById($id);
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess','comben','editPageTitle');
        };
        return $this->callFunction($func, view('backoffice.workforce.employee-payroll.comben.show'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $func = function () use ($id){
            Gate::authorize('updatePolicy', EmployeePayroll::class);
            $breadcrumbs = ['Tenaga Kerja', 'Gaji', 'Compensation Benefit', 'Ubah Compensation Benefit'];
            $pageTitle = "Ubah Compensation Benefit";
            $flashMessageSuccess = session('flashMessageSuccess');
            $comben = $this->employeePayrollCombenService->getCombenById($id);
            $employees = $this->employeeService->getAllEmployeeForSelect();

            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess','comben','employees');

        };

        return $this->callFunction($func, view('backoffice.workforce.employee-payroll.comben.edit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, Request $request)
    {
        $func = function () use ($id, $request){

            if ($request->has('value')) {
                $request->merge([
                    'value' => str_replace(',', '', $request->input('value')), // Remove commas from the single value
                ]);
            }
            $data = $request->validate([
                'employee_id' => 'required|int',
                'date' => 'required|date',
                'note' => 'required|string',
                'value' => 'required|numeric'
            ]);

            $this->employeePayrollCombenService->updateComben($id,$data);
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };

        return $this->callFunction($func, null, 'workforce.employee-payroll-comben.index');

    }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(EmployeeRoleRemunerationPackage $employeeRoleRemunerationPackage)
    // {
    //     //
    // }
}
