<?php

namespace App\Http\Controllers\Api\V1\BackOffice\Base;

use App\Http\Controllers\MasterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaseNotificationController extends MasterController
{

    public function markAllAsRead(Request $request)
    {
        $func = function () {
            $user = Auth::user();

            // Update all unread notifications for the authenticated user
            $user->notificationAlls()->where('is_read', false)->update(['is_read' => true]);

        };

        return $this->callFunction($func, null, null);
    }
}
