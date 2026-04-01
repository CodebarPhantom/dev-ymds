<?php

namespace App\Http\Controllers\Web\Workforce;

use App\Http\Controllers\MasterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Workforce\EmployeePayroll;
use App\Services\Workforce\EmployeePayrollDeductionService;
use App\Services\Workforce\EmployeeService;



class EmployeePayrollDeductionController extends MasterController
{
    protected $employeePayrollDeductionService;
    protected $employeeService;

    public function __construct(
        EmployeePayrollDeductionService $employeePayrollDeductionService,
        EmployeeService $employeeService
    ) {
        parent::__construct();
        $this->employeePayrollDeductionService = $employeePayrollDeductionService;
        $this->employeeService = $employeeService;


    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', EmployeePayroll::class);

            $breadcrumbs = ['Tenaga Kerja', 'Gaji','Deduction'];
            $pageTitle = "Deduction";
            $flashMessageSuccess = session('flashMessageSuccess');
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess');
        };

        return $this->callFunction($func, view('backoffice.workforce.employee-payroll.deduction.index'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $func = function () {
            Gate::authorize('createPolicy', EmployeePayroll::class);
            $breadcrumbs = ['Tenaga Kerja', 'Gaji', 'Tambah Deduction'];
            $pageTitle = "Tambah Deduction";
            $employees = $this->employeeService->getAllEmployeeForSelect();

            $this->data = compact('breadcrumbs', 'pageTitle', 'employees');
        };

        return $this->callFunction($func, view('backoffice.workforce.employee-payroll.deduction.create'));

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

            $this->employeePayrollDeductionService->createDeduction($data);
            $this->messages = ['Deduction berhasil dibuat'];
            session()->flash('flashMessageSuccess');
        };

        return $this->callFunction($func, null, 'workforce.employee-payroll-deduction.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $func = function () use ($id){
            Gate::authorize('readPolicy', EmployeePayroll::class);
            $breadcrumbs = ['Tenaga Kerja', 'Gaji', 'Deduction', 'Lihat Deduction'];
            $pageTitle = "Lihat Deduction";
            $editPageTitle = "Ubah Deduction";
            $flashMessageSuccess = session('flashMessageSuccess');
            $deduction = $this->employeePayrollDeductionService->getDeductionById($id);
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess','deduction','editPageTitle');
        };
        return $this->callFunction($func, view('backoffice.workforce.employee-payroll.deduction.show'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $func = function () use ($id){
            Gate::authorize('updatePolicy', EmployeePayroll::class);
            $breadcrumbs = ['Tenaga Kerja', 'Gaji', 'Deduction', 'Ubah Deduction'];
            $pageTitle = "Ubah Deduction";
            $flashMessageSuccess = session('flashMessageSuccess');
            $deduction = $this->employeePayrollDeductionService->getDeductionById($id);
            $employees = $this->employeeService->getAllEmployeeForSelect();

            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess','deduction','employees');

        };

        return $this->callFunction($func, view('backoffice.workforce.employee-payroll.deduction.edit'));
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

            $this->employeePayrollDeductionService->updateDeduction($id,$data);
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };

        return $this->callFunction($func, null, 'workforce.employee-payroll-deduction.index');

    }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(EmployeeRoleRemunerationPackage $employeeRoleRemunerationPackage)
    // {
    //     //
    // }
}
