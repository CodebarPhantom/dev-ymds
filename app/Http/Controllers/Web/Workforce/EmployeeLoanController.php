<?php

namespace App\Http\Controllers\Web\Workforce;


use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use App\Models\Workforce\EmployeeLoan;
use App\Services\Workforce\EmployeeLoanService;
use App\Services\Workforce\EmployeeService;
use Illuminate\Support\Facades\Gate;



class EmployeeLoanController extends MasterController
{

    protected $employeeLoanService;
    protected $employeeService;



    public function __construct(
        EmployeeLoanService $employeeLoanService,
        EmployeeService $employeeService

    ) {
        parent::__construct();
        $this->employeeLoanService = $employeeLoanService;
        $this->employeeService = $employeeService;

    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', EmployeeLoan::class);

            $breadcrumbs = ['Tenaga Kerja', 'Pinjaman'];
            $pageTitle = "Pinjaman";
            $flashMessageSuccess = session('flashMessageSuccess');
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess');
        };

        return $this->callFunction($func, view('backoffice.workforce.loan.index'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $func = function () {
            Gate::authorize('createPolicy', EmployeeLoan::class);
            $breadcrumbs = ['Tenaga Kerja', 'Pinjaman', 'Tambah Pinjaman'];
            $pageTitle = "Tambah Pinjaman";
            $employees = $this->employeeService->getAllEmployeeForSelect();

            $this->data = compact('breadcrumbs', 'pageTitle', 'employees');
        };

        return $this->callFunction($func, view('backoffice.workforce.loan.create'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('createPolicy', EmployeeLoan::class);

            if ($request->has('loan_amount')) {
                $request->merge([
                    'loan_amount' => str_replace(',', '', $request->input('loan_amount')), // Remove commas from the single value
                ]);
            }

            $data = $request->validate([
                'employee_id' => 'required|int',
                'loan_date' => 'required|date',
                'loan_amount' => 'required|numeric',
                'installment_period' => 'required|int',
                'notes' => 'nullable|string',
            ]);


            $this->employeeLoanService->createLoan($data);
            $this->messages = ['Pinjaman berhasil dibuat'];
            session()->flash('flashMessageSuccess');
        };

        return $this->callFunction($func, null, 'workforce.employee-loan.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($loanId)
    {
        $func = function () use ($loanId) {
            Gate::authorize('readPolicy', EmployeeLoan::class);

            $breadcrumbs = ['Tenaga Kerja', 'Pinjaman', 'Lihat Pinjaman'];
            $pageTitle = "Lihat Pinjaman";
            $editPageTitle = "Ubah Pinjaman";
            $loan = $this->employeeLoanService->showLoan($loanId);
            $employees = $this->employeeService->getAllEmployeeForSelect();

            $this->data = compact('breadcrumbs', 'pageTitle','editPageTitle','loan', 'employees');

        };

        return $this->callFunction($func, view('backoffice.workforce.loan.show'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($loanId)
    {
        $func = function () use ($loanId) {
            Gate::authorize('updatePolicy', EmployeeLoan::class);

            $breadcrumbs = ['Tenaga Kerja', 'Pinjaman', 'Ubah Pinjaman'];
            $pageTitle = "Ubah Pinjaman";
            $loan = $this->employeeLoanService->showLoan($loanId);
            $employees = $this->employeeService->getAllEmployeeForSelect();

            $this->data = compact('breadcrumbs', 'pageTitle','loan', 'employees');

        };

        return $this->callFunction($func, view('backoffice.workforce.loan.edit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $loanId)
    {
        $func = function () use ($loanId, $request) {

            Gate::authorize('updatePolicy', EmployeeLoan::class);
            if ($request->has('loan_amount')) {
                $request->merge([
                    'loan_amount' => str_replace(',', '', $request->input('loan_amount')), // Remove commas from the single value
                ]);
            }

            $data = $request->validate([
                'employee_id' => 'required|int',
                'loan_date' => 'required|date',
                'loan_amount' => 'required|numeric',
                'installment_period' => 'required|int',
                'notes' => 'nullable|string',
            ]);

            $this->employeeLoanService->updateLoan($loanId, $data);
            $this->messages = ['Pinjaman berhasil diubah'];
            session()->flash('flashMessageSuccess');

        };
        return $this->callFunction($func, null, 'workforce.employee-loan.index');

    }


    public function acceleratedRepayment($id)
    {
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', EmployeeLoan::class);

            $this->employeeLoanService->acceleratedRepaymentLoan($id);

            $this->messages = ['Pelunasan dipercepat berhasil dibayrkan'];
            session()->flash('flashMessageSuccess');

        };

        return $this->callFunction($func, null, 'workforce.employee-loan.index');

    }
}
