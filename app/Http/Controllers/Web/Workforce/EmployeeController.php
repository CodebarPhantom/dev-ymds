<?php

namespace App\Http\Controllers\Web\Workforce;

use Illuminate\Http\Request;
use App\Models\Workforce\Employee;
use App\Services\Workforce\EmployeeService;
use App\Http\Controllers\MasterController;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Services\Base\BaseBankService;
use App\Services\DepartementService;
use App\Services\DivisionService;
use App\Services\RoleService;
use App\Services\UserService;
use App\Services\Base\BasePtkpService;

class EmployeeController extends MasterController
{
    protected $employeeService;
    protected $baseBankService;
    protected $divisionService;
    protected $departementService;
    protected $roleService;
    protected $userService;
    protected $basePtkpService;


    // Inject multiple services through the constructor
    public function __construct(
        EmployeeService $employeeService,
        BaseBankService $baseBankService,
        DivisionService $divisionService,
        DepartementService $departementService,
        RoleService $roleService,
        UserService $userService,
        BasePtkpService $basePtkpService
    ) {
        parent::__construct();
        $this->employeeService = $employeeService;
        $this->baseBankService = $baseBankService;
        $this->divisionService = $divisionService;
        $this->departementService = $departementService;
        $this->roleService = $roleService;
        $this->userService = $userService;
        $this->basePtkpService = $basePtkpService;
    }

    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', Employee::class);

            $breadcrumbs = ['Tenaga Kerja', 'Karyawan'];
            $pageTitle = "Karyawan";
            $flashMessageSuccess = session('flashMessageSuccess');
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess');
        };

        return $this->callFunction($func, view('backoffice.workforce.employee.index'));
    }


    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', Employee::class);

            $breadcrumbs = ['Tenaga Kerja', 'Karyawan', 'Tambah Karyawan'];
            $pageTitle = "Tambah Karyawan";
            $baseBanks = $this->baseBankService->getAllBaseBank();
            $divisions = $this->divisionService->getAllDivisionForSelect();
            $departments = $this->departementService->getAllDepartments();
            $roles = $this->roleService->getAllRole();
            $employees = $this->employeeService->getAllEmployees();
            $basePtkps = $this->basePtkpService->getAllPtkp();

            $this->data = compact('breadcrumbs', 'pageTitle', 'baseBanks', 'divisions', 'departments', 'roles','employees','basePtkps');

        };


        return $this->callFunction($func, view('backoffice.workforce.employee.create'));
    }

    public function show($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', Employee::class);

            $breadcrumbs = ['Tenaga Kerja', 'Karyawan', 'Lihat Karyawan'];
            $pageTitle = "Lihat Karyawan";
            $editPageTitle = "Ubah Karyawan";

            $employee = $this->employeeService->showEmployee($id);

            $this->data = compact('breadcrumbs', 'pageTitle', 'editPageTitle', 'employee');
        };

        return $this->callFunction($func, view('backoffice.workforce.employee.show'));
    }

    public function edit($id)
    {
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', Employee::class);
            $breadcrumbs = ['Tenaga Kerja', 'Karyawan', 'Ubah Karyawan'];
            $pageTitle = "Ubah Karyawan";

            $employee = $this->employeeService->showEmployee($id);
            $baseBanks = $this->baseBankService->getAllBaseBank();
            $divisions = $this->divisionService->getAllDivisionForSelect();
            $departments = $this->departementService->getAllDepartments();
            $roles = $this->roleService->getAllRole();
            $employees = $this->employeeService->getAllEmployeeWithoutSelectedId($id);
            $basePtkps = $this->basePtkpService->getAllPtkp();



            $this->data = compact('breadcrumbs', 'pageTitle', 'employee', 'baseBanks', 'divisions', 'departments', 'roles','employees','basePtkps');
        };

        return $this->callFunction($func, view('backoffice.workforce.employee.edit'));
    }

    public function editUser($id)
    {
        $func = function () use ($id) {
            Gate::authorize('createPolicy', Employee::class);
            $breadcrumbs = ['Tenaga Kerja', 'Karyawan', 'User Karyawan'];
            $pageTitle = "Perbarui User Karyawan";

            $employee = $this->employeeService->showEmployee($id);
            $user = $this->userService->showUserByEmployeeId($id);
            $this->data = compact('breadcrumbs', 'pageTitle', 'employee', 'user');
        };

        return $this->callFunction($func, view('backoffice.workforce.employee.user.edit'));
    }

    public function updateUser($id, Request $request)
    {
        $func = function () use ($id, $request) {

            $employee = User::where('employee_id',$id)->first();
            // Validate the request data
            $data = $request->validate([
                'email' => 'required|string|email|max:255|unique:users,email,' . ($employee->id ?? 'NULL'), // Use 'NULL' to avoid invalid bigint comparison
                'password' => 'nullable|string|min:8|confirmed', // Password is optional during update
                'url_image' => 'nullable|file|max:2048', // Image file is optional, with max size 2MB
            ]);

            // Check if a file is uploaded and add it to the data array
            if ($request->hasFile('url_image')) {
                $data['url_image'] = $request->file('url_image');
            }

            $employee = Employee::findOrFail($id);
            $data['name'] = $employee->name;

            // Call the service method to create or update the user
            $this->userService->createOrUpdateUserByEmployeeId($id, $data);

            session()->flash('flashMessageSuccess', 'Task was successful!');
        };

        return $this->callFunction($func, null, 'workforce.employee.index');
    }
}
