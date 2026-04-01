<?php

namespace App\Services\Workforce;

use App\Models\Role;
use App\Models\Workforce\EmployeeRoleRemunerationPackage;
use App\Services\MasterService;

class EmployeeRoleRemunerationPackageService extends MasterService
{

    public function getAllRoleWithRemuneration()
    {
        return Role::with(['remunerationPackages'])->active()->get();
    }

    public function getRoleByIdWithRemuneration($id)
    {
        return Role::with(['remunerationPackages'])->where('id', $id)->first();
    }

    public function deleteRemuneration($id)
    {
        return EmployeeRoleRemunerationPackage::destroy($id);
    }

    public function updateRemunerationPackage($roleId, array $data)
    {
        // Fetch existing records for this role
        $existingPackages = EmployeeRoleRemunerationPackage::where('role_id', $roleId)->pluck('id')->toArray();

        // Track IDs that are updated or added
        $processedIds = [];

        foreach ($data['note'] as $index => $note) {
            $value = $data['value'][$index];
            $id = $data['id'][$index] ?? null;

           // dd($id);

            if ($id) {
                // Update existing record
                $package = EmployeeRoleRemunerationPackage::find($id);
                if ($package) {
                    $package->update([
                        'note' => $note,
                        'value' => $value,
                    ]);
                    $processedIds[] = $package->id;
                }
            } else {
                // Create new record
                $newPackage = EmployeeRoleRemunerationPackage::create([
                    'role_id' => $roleId,
                    'note' => $note,
                    'value' => $value,
                ]);
                $processedIds[] = $newPackage->id;
            }
        }

        // Delete records that are not in the submitted IDs
        $idsToDelete = array_diff($existingPackages, $processedIds);
        EmployeeRoleRemunerationPackage::whereIn('id', $idsToDelete)->delete();

        return;
    }
}
