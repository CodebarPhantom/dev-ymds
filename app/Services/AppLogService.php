<?php

// app/Services/LogService.php

namespace App\Services;

use App\Models\AppLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class AppLogService
{


    public function logChange(Model $model, string $activity, string $code = null)
    {
        $request = null;

        // Default responses based on activity types
        $activityDefaults = [
            'created'       => '201',   // Created
            'updated'       => '200',   // OK
            'deleted'       => '204',   // No Content
            'not_found'     => '404',   // Not Found
            'bad_request'   => '400',   // Bad Request
            'unauthorized'  => '401',   // Unauthorized
            'forbidden'     => '403',   // Forbidden
            'server_error'  => '500',   // Internal Server Error
        ];

        // Set response code based on activity or default to provided code
        $response = $code ?? ($activityDefaults[$activity] ?? '200');

        // Determine request payload based on activity type
        if ($activity === 'created') {
            $request = json_encode($model->getAttributes());
        } elseif ($activity === 'updated') {
            $request = json_encode($model->getChanges());
        } elseif ($activity === 'deleted') {
            $request = json_encode($model->getOriginal());
        } else {
            $request = 'N/A'; // For non-CRUD activities like errors
        }

        AppLog::create([
            'user_id' => Auth::id() ?? null,
            'model' => get_class($model),
            'activity' => $activity,
            'request' => $request,
            'response' => $response, // Use the default or provided code
        ]);
    }
}
