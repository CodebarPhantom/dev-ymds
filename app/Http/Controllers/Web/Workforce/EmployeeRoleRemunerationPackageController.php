<?php

namespace App\Http\Controllers\Web\Workforce;

use App\Http\Controllers\MasterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Workforce\EmployeePayroll;
use App\Services\Workforce\EmployeeRoleRemunerationPackageService;



class EmployeeRoleRemunerationPackageController extends MasterController
{
    protected $employeeRoleRemunerationPackageService;

    public function __construct(
        EmployeeRoleRemunerationPackageService $employeeRoleRemunerationPackageService
    ) {
        parent::__construct();
        $this->employeeRoleRemunerationPackageService = $employeeRoleRemunerationPackageService;

    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', EmployeePayroll::class);

            $breadcrumbs = ['Tenaga Kerja', 'Gaji','Kelola Paket Remunerasi'];
            $pageTitle = "Kelola Paket Remunerasi";
            $flashMessageSuccess = session('flashMessageSuccess');
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess');
        };

        return $this->callFunction($func, view('backoffice.workforce.employee-payroll.remuneration-packages.index'));
    }

    // /**
    //  * Show the form for creating a new resource.
    //  */
    // public function create()
    // {
    //     //
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {
    //     //
    // }

    /**
     * Display the specified resource.
     */
    public function show($roleId)
    {
        $func = function () use ($roleId){
            Gate::authorize('readPolicy', EmployeePayroll::class);
            $breadcrumbs = ['Tenaga Kerja', 'Gaji', 'Kelola Paket Remunerasi', 'Lihat Paket Remunerasi'];
            $pageTitle = "Lihat Paket Remunerasi";
            $editPageTitle = "Ubah Paket Remunerasi";
            $flashMessageSuccess = session('flashMessageSuccess');
            $role = $this->employeeRoleRemunerationPackageService->getRoleByIdWithRemuneration($roleId);
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess','role','editPageTitle');
        };
        return $this->callFunction($func, view('backoffice.workforce.employee-payroll.remuneration-packages.show'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($roleId)
    {
        $func = function () use ($roleId){
            Gate::authorize('updatePolicy', EmployeePayroll::class);
            $breadcrumbs = ['Tenaga Kerja', 'Gaji', 'Kelola Paket Remunerasi', 'Ubah Paket Remunerasi'];
            $pageTitle = "Ubah Paket Remunerasi";
            $flashMessageSuccess = session('flashMessageSuccess');
            $role = $this->employeeRoleRemunerationPackageService->getRoleByIdWithRemuneration($roleId);
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess','role');

        };

        return $this->callFunction($func, view('backoffice.workforce.employee-payroll.remuneration-packages.edit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($roleId, Request $request)
    {
        $func = function () use ($roleId, $request){
            if ($request->has('value')) {
                $request->merge([
                    'value' => array_map(function ($value) {
                        return str_replace(',', '', $value); // Remove commas
                    }, $request->input('value')),
                ]);
            }

            // Validate the data
            $data = $request->validate([
                'note.*' => 'required|string|max:255',
                'value.*' => 'required|numeric',
                'id.*' => 'nullable|integer',
            ]);

            $this->employeeRoleRemunerationPackageService->updateRemunerationPackage($roleId, $data);
            $this->messages = ['Paket Remunerasi berhasil diubah'];
            session()->flash('flashMessageSuccess');
        };

        return $this->callFunction($func, null, 'workforce.employee-role-remuneration-package.index');

    }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(EmployeeRoleRemunerationPackage $employeeRoleRemunerationPackage)
    // {
    //     //
    // }
}
