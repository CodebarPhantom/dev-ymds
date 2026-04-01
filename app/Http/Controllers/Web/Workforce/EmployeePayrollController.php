<?php

namespace App\Http\Controllers\Web\Workforce;

use App\Enums\GlobalParam;
use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use App\Models\Workforce\EmployeePayroll;
use App\Services\Workforce\EmployeePayrollService;
use Illuminate\Support\Facades\Gate;
use App\Helpers\NumberToWords;

class EmployeePayrollController extends MasterController
{
    protected $employeePayrollService;

    public function __construct(
        EmployeePayrollService $employeePayrollService
    ) {
        parent::__construct();
        $this->employeePayrollService = $employeePayrollService;

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', EmployeePayroll::class);

            $breadcrumbs = ['Tenaga Kerja', 'Payroll'];
            $pageTitle = "Payroll";
            $flashMessageSuccess = session('flashMessageSuccess');
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess');
        };

        return $this->callFunction($func, view('backoffice.workforce.employee-payroll.payroll.index'));
    }

    public function generate(Request $request)
    {
        $func = function () use($request) {
            Gate::authorize('readPolicy', EmployeePayroll::class);

            $employeePayrolls = $this->employeePayrollService->generatePayroll($request->month, $request->year);
            $this->data = compact('employeePayrolls');
        };

        return $this->callFunction($func, null ,'workforce.employee-payroll.index');
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
    public function show($employeePayrollId)
    {
        $func = function () use ($employeePayrollId) {
            Gate::authorize('readPolicy', EmployeePayroll::class);

            $breadcrumbs = ['Tenaga Kerja', 'Payroll'];
            $pageTitle = "Payroll";
            $flashMessageSuccess = session('flashMessageSuccess');
            $employeePayroll = $this->employeePayrollService->getEmployeePayrollById($employeePayrollId);
            $salaryInWords = NumberToWords::numberToWords($employeePayroll['salary_thp']);
            $acknowledgeName = GlobalParam::PAYSLIP_ACKNOWLEDGE_NAME->value;
            $acknowledgeRole = GlobalParam::PAYSLIP_ACKNOWLEDGE_ROLE->value;
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess','employeePayroll','salaryInWords','acknowledgeName','acknowledgeRole');
        };

        return $this->callFunction($func, view('backoffice.workforce.employee-payroll.payroll.payslip'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeePayroll $employeePayroll)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeePayroll $employeePayroll)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeePayroll $employeePayroll)
    {
        //
    }
}
