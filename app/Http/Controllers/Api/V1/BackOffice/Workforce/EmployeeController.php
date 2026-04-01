<?php

namespace App\Http\Controllers\Api\V1\BackOffice\Workforce;

use App\Http\Controllers\MasterController;
use App\Models\Workforce\Employee;
use App\Models\Workforce\EmployeeFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Services\Workforce\EmployeeService;
use Carbon\Carbon;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeController extends MasterController
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        parent::__construct();
        $this->employeeService = $employeeService;
    }

    public function dataTable(Request $request)
    {

        Gate::authorize('readPolicy', Employee::class);

        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10
        $sortField = $request->input('sortField', 'name'); // Default sort field
        $sortOrder = $request->input('sortOrder', 'asc'); // Default sort order

        // Validate sort field and order
        $allowedSortFields = ['name']; // Add your sortable fields
        $allowedSortOrders = ['asc', 'desc'];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'name'; // Fallback to default
        }

        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'asc'; // Fallback to default
        }
        $employees = Employee::with(['role', 'department', 'division', 'user'])
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->whereRaw('name ILIKE ?', ['%' . $search . '%'])
                        ->orWhereRaw('employee_code ILIKE ?', ['%' . $search . '%']);
                }
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($pageSize);

        // Prepare the response
        return response()->json([
            'page' => $employees->currentPage(),
            'pageCount' => $employees->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $employees->total(),
            'data' =>  $employees->items(),
        ]);
    }

    public function store(Request $request)
    {
        $func = function () use ($request) {


            Gate::authorize('createPolicy', Employee::class);

            // Step 1: Define validation rules for employee data
            $data = $request->validate($this->rules());

            $employee =  $this->employeeService->createEmployee($data);
            $this->data = compact('employee');
            $this->code = 201;
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };

        return $this->callFunction($func);
    }

    public function update(Request $request, $id)
    {

        $func = function () use ($request, $id) {
            Gate::authorize('updatePolicy', Employee::class);
            $data = $request->validate($this->rules());

            //Log::debug($data);

            $employee =  $this->employeeService->updateEmployee($id, $data);
            $this->data = compact('employee');
            $this->code = 201;
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };

        return $this->callFunction($func);
    }


    public function uploadFiles(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('createPolicy', Employee::class);

            $validatedData = $request->validate([
                'files.*' => 'required|file|max:2048', // 2MB max size per file (adjust as needed)
                'employee_id' => 'integer|nullable'
            ]);
            $uploadedFiles = $this->employeeService->uploadFiles($request->file('files'), $validatedData['employee_id']);
            $this->data = compact('uploadedFiles');
            $this->messages = ['Files uploaded successfully!'];
        };

        return $this->callFunction($func);
    }

    public function deleteFile($id)
    {
        $func = function () use ($id) {
            Gate::authorize('createPolicy', Employee::class);
            $deleteFile = $this->employeeService->deleteFile($id);
            $this->messages = [$deleteFile['message']];
            $this->code = $deleteFile['code'];
        };

        return $this->callFunction($func);
    }

    public function deleteWorkHistory($id)
    {
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', Employee::class);

            $this->employeeService->deleteWorkhistory($id);
            $this->messages = ['Riwayat Pekerjaan Berhasil di Hapus'];
            $this->code = 200;
        };

        return $this->callFunction($func);
    }

    public function deleteEducationHistory($id)
    {
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', Employee::class);

            $this->employeeService->deleteEducationhistory($id);
            $this->messages = ['Riwayat Pendidikan Berhasil di Hapus'];
            $this->code = 200;
        };

        return $this->callFunction($func);
    }

    public function deleteEmployeeAgreement($id)
    {
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', Employee::class);

            $this->employeeService->deleteEmployeeAgreement($id);
            $this->messages = ['Perjanjian Kerja Berhasil di Hapus'];
            $this->code = 200;
        };

        return $this->callFunction($func);
    }

    public function getAllEmployees()
    {
        $func = function () {
            //Gate::authorize('readPolicy', Employee::class);

            $employeesData = $this->employeeService->getAllEmployees()->select('id', 'name')->toArray();

            $employees = array_map(function ($item) {
                return [
                    "id" => $item["id"],
                    "label" => $item["name"], // Change 'name' to 'label'
                ];
            }, $employeesData);

            $this->data = compact('employees');
        };

        return $this->callFunction($func);
    }

    public function getAllRotatingShiftEmployees()
    {
        $func = function () {
            //Gate::authorize('readPolicy', Employee::class);

            $employeesData = $this->employeeService->getAllEmployeeRotatingShift()->select('id', 'name')->toArray();

            $employeeShifts = array_map(function ($item) {
                return [
                    "id" => $item["id"],
                    "label" => $item["name"], // Change 'name' to 'label'
                ];
            }, $employeesData);

            $this->data = compact('employeeShifts');
        };

        return $this->callFunction($func);
    }

    /**
     * Validation rules for storing and updating employees.
     *
     * @return array
     */
    private function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'leave' => 'required|integer',
            'no_ktp' => 'required|string|max:255',
            'pob' => 'required|string|max:255',
            'dob' => 'required|date',
            'start_work_date' => 'required|date',
            'address' => 'required|string|max:255',
            'residence_status' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'religion' => 'required|string|max:255',
            'gender' => 'required|string|in:Laki-laki,Perempuan',
            'salary_method' => 'required|string|max:255',
            'salary_value' => 'required|numeric',
            'bank_id' => 'required|integer',
            'bank_account_number' => 'required|string|max:255',
            'division_id' => 'required|integer',
            'department_id' => 'required|integer',
            'role_id' => 'required|integer',
            'report_to_employee_id' => 'nullable|integer',
            'employee_status' => 'required|string|max:255',
            'is_active' => 'required|boolean',
            'uploaded_file_ids' => 'nullable',

            // Optional fields
            'number_of_dependents' => 'nullable|integer',
            'body_height' => 'nullable|numeric',
            'body_weight' => 'nullable|numeric',
            'blood_group' => 'nullable|string|max:5',
            'url_photo' => 'nullable|string|max:255',
            'bpjs_tk_account_number' => 'nullable|string|max:255',
            'bpjs_ks_account_number' => 'nullable|string|max:255',
            'npwp_account_number' => 'nullable|string|max:255',
            'ptkp_id' => 'nullable|integer',
            'health_history' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relation' => 'nullable|string|max:255',
            'emergency_contact_address' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',

            // Work history
            'work_history.*.company_name' => 'required|string|max:255',
            'work_history.*.role_name' => 'required|string|max:255',
            'work_history.*.start_date' => 'required|date',
            'work_history.*.end_date' => 'required|date|after_or_equal:work_history.*.start_date',
            'work_history.*.reason' => 'nullable|string|max:255',
            'work_history.*.salary' => 'required|numeric',

            // Education history
            'education_history.*.education_name' => 'required|string|max:255',
            'education_history.*.city' => 'required|string|max:255',
            'education_history.*.start_year' => 'required|integer',
            'education_history.*.end_year' => 'required|integer|gte:education_history.*.start_year',
            'education_history.*.major' => 'nullable|string|max:255',

            //Agreement
            'employee_agreement.*.agreement_name' => 'nullable|string|max:255',
            'employee_agreement.*.start_date' => 'date',
            'employee_agreement.*.end_date' => 'date|after_or_equal:employee_agreement.*.start_date',
            //'employee_agreement.*.is_active'=>'boolean',

        ];
    }
}
