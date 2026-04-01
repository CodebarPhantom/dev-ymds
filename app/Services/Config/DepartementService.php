<?php

namespace App\Services;

use App\Models\Departement;
use App\Services\MasterService;

class DepartementService extends MasterService
{
    public function storeDepartement(array $data)
    {
        $department = Departement::create($data);
        $this->appLogService->logChange($department, 'created');
        return $department;

    }

    public function showDepartement(int $id)
    {
        return Departement::where('id',$id)->first();
    }

    public function updateDepartement($id, array $data)
    {
        $department = Departement::find($id);
        $department->update($data);
        $this->appLogService->logChange($department, 'updated');
        return $department;
    }

    public function deleteDepartement($id)
    {
        $department = Departement::findOrFail($id);
        if ($department->delete()) {
            $this->appLogService->logChange($department, 'deleted');
        }
        return $department;
    }

    public function getAllDepartments()
    {
        return Departement::active()->orderBy('name','asc')->get();
    }

    public function getAllDepartmentsForSelect( $divisionId)
    {
        return Departement::when($divisionId, function ($query, $divisionId) {
            return $query->where('division_id', $divisionId);
            })
            ->active()
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($department) {
            return [
                'id' => $department->id,
                'label' => $department->name,
            ];
            });
    }

}
