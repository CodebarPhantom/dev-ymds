<?php

namespace App\Http\Controllers\Api\V1\BackOffice\MyActivity;

use App\Models\Workforce\EmployeeLeave;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MasterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class MyLeaveController extends MasterController
{
    public function dataTable(Request $request){


        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10

        $myLeaves = EmployeeLeave::where('employee_id',Auth::user()->employee_id)
        ->orderBy('created_at', 'desc')
        ->paginate($pageSize);
                       // Prepare the response
        return response()->json([
            'page' => $myLeaves->currentPage(),
            'pageCount' => $myLeaves->lastPage(),
            'sortField' => 'created_at',
            'sortOrder' => 'desc',
            'totalCount' => $myLeaves->total(),
            'data' =>  $myLeaves->items(),
        ]);
    }

    public function dataTableConfirmLeave(Request $request){

        Gate::authorize('approvePolicy', EmployeeLeave::class);

        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10

        $myLeaves = EmployeeLeave::with('employee')->where('known_by',Auth::user()->employee_id)
        ->orderBy('created_at', 'desc')
        ->paginate($pageSize);
                       // Prepare the response
        return response()->json([
            'page' => $myLeaves->currentPage(),
            'pageCount' => $myLeaves->lastPage(),
            'sortField' => 'created_at',
            'sortOrder' => 'desc',
            'totalCount' => $myLeaves->total(),
            'data' =>  $myLeaves->items(),
        ]);
    }

}
