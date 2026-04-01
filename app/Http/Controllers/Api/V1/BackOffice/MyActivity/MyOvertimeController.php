<?php

namespace App\Http\Controllers\Api\V1\BackOffice\MyActivity;

use App\Models\Workforce\EmployeeLeave;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MasterController;
use App\Models\Workforce\EmployeeOvertime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class MyOvertimeController extends MasterController
{
    public function dataTable(Request $request){


        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10

        $myOverimes = EmployeeOvertime::with(['shift'])->where('employee_id',Auth::user()->employee_id)
        ->orderBy('created_at', 'desc')
        ->paginate($pageSize);
                       // Prepare the response
        return response()->json([
            'page' => $myOverimes->currentPage(),
            'pageCount' => $myOverimes->lastPage(),
            'sortField' => 'created_at',
            'sortOrder' => 'desc',
            'totalCount' => $myOverimes->total(),
            'data' =>  $myOverimes->items(),
        ]);
    }

    public function dataTableConfirmOvertime(Request $request){

        Gate::authorize('approvePolicy', EmployeeOvertime::class);

        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10

        $myOverimes = EmployeeOvertime::with(['employee','shift'])->where('known_by',Auth::user()->employee_id)
        ->orderBy('created_at', 'desc')
        ->paginate($pageSize);
                       // Prepare the response
        return response()->json([
            'page' => $myOverimes->currentPage(),
            'pageCount' => $myOverimes->lastPage(),
            'sortField' => 'created_at',
            'sortOrder' => 'desc',
            'totalCount' => $myOverimes->total(),
            'data' =>  $myOverimes->items(),
        ]);
    }

}
