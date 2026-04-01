<?php

namespace App\Services\Base;

use App\Models\Base\BaseNotification;
use App\Services\MasterService;

class BaseNotificationService
{
    // public function getAllBaseNotification()
    // {
    //     return BaseNotification::byUserId()->limit(10)->get();
    // }

    public function createNotification($title, $description, int $toUserId = null, int $departmentId = null, bool $forAllUser = false)
    {
        return BaseNotification::create([
            'title'=>$title,
            'description'=>$description,
            'user_id'=> $toUserId,
            'department_id' => $departmentId,
            'for_all_user' => $forAllUser,
        ]);
    }
}
