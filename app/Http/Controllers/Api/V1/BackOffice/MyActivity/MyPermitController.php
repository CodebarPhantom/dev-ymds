<?php

namespace App\Http\Controllers\Api\V1\BackOffice\MyActivity;

use App\Models\Workforce\EmployeePermit;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MasterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MyPermitController extends MasterController
{
    public function dataTable(Request $request){


        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10

        $myPermit = EmployeePermit::where('employee_id',Auth::user()->employee_id)
        ->orderBy('created_at', 'desc')
        ->paginate($pageSize);
                       // Prepare the response
        return response()->json([
            'page' => $myPermit->currentPage(),
            'pageCount' => $myPermit->lastPage(),
            'sortField' => 'created_at',
            'sortOrder' => 'desc',
            'totalCount' => $myPermit->total(),
            'data' =>  $myPermit->items(),
        ]);
    }

    public function dataTableConfirmPermit(Request $request){

        Gate::authorize('approvePolicy', EmployeePermit::class);

        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10

        $myPermit = EmployeePermit::with('employee')->where('known_by',Auth::user()->employee_id)
        ->orderBy('created_at', 'desc')
        ->paginate($pageSize);
                       // Prepare the response
        return response()->json([
            'page' => $myPermit->currentPage(),
            'pageCount' => $myPermit->lastPage(),
            'sortField' => 'created_at',
            'sortOrder' => 'desc',
            'totalCount' => $myPermit->total(),
            'data' =>  $myPermit->items(),
        ]);
    }

}
