<?php

namespace App\Services\Workforce;

use App\Models\Role;
use App\Models\Workforce\Employee;
use App\Models\Workforce\EmployeeAgreement;
use App\Models\Workforce\EmployeeEducationHistory;
use App\Models\Workforce\EmployeeWorkHistory;
use App\Models\Workforce\EmployeeFile;
use App\Services\MasterService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use App\Models\User;
use App\Models\WorkSchedule\ShiftFixed;
use App\Models\WorkSchedule\ShiftFixedRole;

class EmployeeService extends MasterService
{
    public function createEmployee(array $data)
    {

        // 1. Create the Employee record
        $employeeData = Arr::except($data, ['work_history', 'education_history', 'employee_agreement']); // Remove work and education histories

        //Generate Employee Code
        $employeeCount = Employee::count() + 1;
        $employeeCode = '2204' . str_pad($employeeCount, 5, '0', STR_PAD_LEFT); // Pads the number with leading zeros
        $employeeData['employee_code'] = $employeeCode;

        $employee = Employee::create($employeeData);
        $this->appLogService->logChange($employee, 'created');


        if (isset($data['uploaded_file_ids'])) {
            EmployeeFile::whereIn('id', explode(',', $data['uploaded_file_ids']))->update(['employee_id' => $employee->id]);
        }
        // 2. Handle work history
        if (isset($data['work_history'])) {
            foreach ($data['work_history'] as $work) {
                // Add the employee_id before saving work history
                $work['employee_id'] = $employee->id;

                // Save each work history entry
                $employeeWorkHistory = EmployeeWorkHistory::create($work);
                $this->appLogService->logChange($employeeWorkHistory, 'created');
            }
        }

        // 3. Handle education history
        if (isset($data['education_history'])) {
            foreach ($data['education_history'] as $education) {
                // Add the employee_id before saving education history
                $education['employee_id'] = $employee->id;

                // Save each education history entry
                $employeeEducationHistory = EmployeeEducationHistory::create($education);
                $this->appLogService->logChange($employeeEducationHistory, 'created');
            }
        }

        if (isset($data['employee_agreement'])) {
            foreach ($data['employee_agreement'] as $agreement) {
                // Add the employee_id before saving education history
                $agreement['employee_id'] = $employee->id;

                // Save each education history entry
                $employeeAgreement = EmployeeAgreement::create($agreement);
                $this->appLogService->logChange($employeeAgreement, 'created');
            }
        }
        // Optionally return the employee with relationships loaded
        return $employee->load(['workHistories', 'educationHistories', 'employeeAgreements']);
    }

    public function showEmployee(int $id)
    {
        return Employee::with(['educationHistories', 'workHistories', 'employeeAgreements', 'files', 'department', 'division', 'role', 'bank', 'reportTo'])->where('id', $id)->first();
    }

    public function getEmployeeById(int $id)
    {
        return Employee::find($id);
    }

    public function updateEmployee($id, array $data)
    {
        $employeeData = Arr::except($data, ['work_history', 'education_history', 'employee_agreement', 'uploaded_file_ids']);

        // 2. Update employee main data
        Employee::where('id', $id)->update($employeeData);
        $employee = Employee::find($id); // Fetch the employee after updating
        $this->appLogService->logChange($employee, 'updated');

        $user = User::where('employee_id', $id)->first();
        if ($user) {
            $role = Role::find($employee->role_id);
            $user->syncRoles([$role]);
        }


        // 3. Handle work history
        if (isset($data['work_history'])) {
            $existingWorkHistoryIds = collect($data['work_history'])->pluck('id')->filter()->toArray();

            // Delete work histories that were removed in the request
            EmployeeWorkHistory::where('employee_id', $employee->id)
                ->whereNotIn('id', $existingWorkHistoryIds)
                ->delete();

            foreach ($data['work_history'] as $work) {
                if (isset($work['id'])) {
                    // If the work history has an ID, update the existing record
                    EmployeeWorkHistory::where('id', $work['id'])
                        ->where('employee_id', $employee->id)
                        ->update(Arr::except($work, ['id']));
                } else {
                    // Add the employee_id before creating a new record
                    $work['employee_id'] = $employee->id;
                    EmployeeWorkHistory::create($work);
                }
            }
        }

        // 4. Handle education history
        if (isset($data['education_history'])) {
            $existingEducationHistoryIds = collect($data['education_history'])->pluck('id')->filter()->toArray();

            // Delete education histories that were removed in the request
            EmployeeEducationHistory::where('employee_id', $employee->id)
                ->whereNotIn('id', $existingEducationHistoryIds)
                ->delete();

            foreach ($data['education_history'] as $education) {
                if (isset($education['id'])) {
                    // If the education history has an ID, update the existing record
                    EmployeeEducationHistory::where('id', $education['id'])
                        ->where('employee_id', $employee->id)
                        ->update(Arr::except($education, ['id']));
                } else {
                    // Add the employee_id before creating a new record
                    $education['employee_id'] = $employee->id;
                    EmployeeEducationHistory::create($education);
                }
            }
        }

        if (isset($data['employee_agreement'])) {
            $existingEmployeeAgreementIds = collect($data['employee_agreement'])->pluck('id')->filter()->toArray();

            // Delete education histories that were removed in the request
            EmployeeAgreement::where('employee_id', $employee->id)
                ->whereNotIn('id', $existingEmployeeAgreementIds)
                ->delete();

            foreach ($data['employee_agreement'] as $agreement) {
                if (isset($agreement['id'])) {
                    // If the education history has an ID, update the existing record
                    EmployeeAgreement::where('id', $agreement['id'])
                        ->where('employee_id', $employee->id)
                        ->update(Arr::except($agreement, ['id']));
                } else {
                    // Add the employee_id before creating a new record
                    $agreement['employee_id'] = $employee->id;
                    EmployeeAgreement::create($agreement);
                }
            }
        }

        return $employee->load(['workHistories', 'educationHistories']);
    }

    public function deleteEmployee($id)
    {
        return Employee::destroy($id);
    }

    public function getAllEmployees()
    {
        return Employee::active()->orderBy('name', 'asc')->get();
    }

    public function getAllEmployeeWithoutSelectedId($id)
    {
        return Employee::active()
            ->where('id', '!=', $id)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getAllEmployeeRotatingShift()
    {
        $roleFixedShift = ShiftFixedRole::distinct()->pluck('role_id')->toArray();
        return Employee::active()
            ->whereNotIn('role_id', $roleFixedShift) // Exclude these role IDs
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getAllEmployeeByRoleId($roleId)
    {
        return Employee::active()->where('role_id',$roleId)->orderBy('name','asc')->get();
    }

    public function uploadFiles(array $files, $employeeId)
    {
        $uploadedFiles = [];

        // Loop through each file in the files array
        foreach ($files as $file) {

            // Store the file in the 'employee_files' directory within the 'uploads' disk
            $path = $file->store('employee_files', 'uploads');

            // Generate the original file name (without extension)
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            // Get file extension
            $extension = $file->getClientOriginalExtension();

            // Create a slugified file name with timestamp for uniqueness
            $slugifiedName = Str::slug($originalName) . '-' . time() . '.' . $extension;

            // Save file details to the database (optional)
            $fileRecord = EmployeeFile::create([
                'name' => $originalName, // Original name without slug
                'slug' => $slugifiedName, // Slugified file name
                'path' => 'uploads/' . $path, // Path where file is stored
                'employee_id' => $employeeId,
                'mime_type' => $file->getMimeType(),
                'extension' => $extension,
                'size' => $file->getSize(),
            ]);

            // Add the uploaded file details to the response array
            $uploadedFiles[] = [
                'id' => $fileRecord->id,
                'name' => $fileRecord->name,
                'slug' => $fileRecord->slug,
                'path' => 'uploads/' . $fileRecord->path,
                'employee_id' => $employeeId,
                'mime_type' => $fileRecord->mime_type,
                'extension' => $fileRecord->extension,
                'size' => $fileRecord->size,
            ];
        }

        // Return the list of uploaded files
        return $uploadedFiles;
    }

    public function deleteFile($id)
    {
        // Find the file by ID
        $file = EmployeeFile::find($id);

        if (!$file) {
            return ['message' => 'File not found!', 'code' => 404];
        }

        // Delete the file from the storage
        Storage::disk('uploads')->delete($file->path);

        // Remove the file record from the database
        $file->delete();

        return ['message' => 'File deleted successfully!', 'code' => 200];
    }

    public function deleteWorkHistory($id)
    {
        return EmployeeWorkHistory::destroy($id);
    }

    public function deleteEducationHistory($id)
    {
        return EmployeeEducationHistory::destroy($id);
    }

    public function deleteEmployeeAgreement($id)
    {
        return EmployeeAgreement::destroy($id);
    }

    public function getAllEmployeeForSelect()
    {
        return Employee::active()
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'label' => $employee->name,
                ];
            });
    }
}
