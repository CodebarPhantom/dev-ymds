<?php

namespace App\Services;

use App\Models\Entity;
use App\Services\MasterService;

class EntityService extends MasterService
{
    public function storeEntity(array $data)
    {
        $entity = Entity::create($data);
        $this->appLogService->logChange($entity, 'created');
        return $entity;

    }

    public function showEntity(int $id)
    {
        return Entity::where('id',$id)->first();
    }

    public function updateEntity($id, array $data)
    {
        $entity = Entity::find($id);
        $entity->update($data);
        $this->appLogService->logChange($entity, 'updated');
        return $entity;
    }

    public function deleteEntity($id)
    {
        $entity = Entity::findOrFail($id);
        if ($entity->delete()) {
            $this->appLogService->logChange($entity, 'deleted');
        }
        return $entity;
    }

    public function getAllEntities()
    {
        return Entity::active()->get();
    }

}
