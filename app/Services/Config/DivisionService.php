<?php

namespace App\Services;

use App\Models\Division;
use App\Services\MasterService;

class DivisionService extends MasterService
{
    public function storeDivision(array $data)
    {
        $division = Division::create($data);
        $this->appLogService->logChange($division, 'created');
        return $division;
    }

    public function showDivision(int $id)
    {
        return Division::where('id', $id)->first();
    }

    public function updateDivision($id, array $data)
    {
        $division = Division::find($id);
        $division->update($data);
        $this->appLogService->logChange($division, 'updated');
        return $division;
    }

    public function deleteDivision($id)
    {
        $division = Division::findOrFail($id);
        if ($division->delete()) {
            $this->appLogService->logChange($division, 'deleted');
        }
        return $division;
    }

    public function getAllDivisions()
    {
        return Division::active()->orderBy('name', 'asc')->get();
    }

    public function getAllDivisionForSelect()
    {
        return Division::active()
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($division) {
                return [
                    'id' => $division->id,
                    'label' => $division->name,
                ];
            });
    }
}
