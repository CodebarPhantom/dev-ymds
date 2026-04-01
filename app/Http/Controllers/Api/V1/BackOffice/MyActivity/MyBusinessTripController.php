<?php

namespace App\Http\Controllers\Api\V1\BackOffice\MyActivity;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MasterController;
use App\Models\Workforce\EmployeeBusinessTrip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MyBusinessTripController extends MasterController
{
    public function dataTable(Request $request){


        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10

        $myBusinessTrip = EmployeeBusinessTrip::where('employee_id',Auth::user()->employee_id)
        ->orderBy('created_at', 'desc')
        ->paginate($pageSize);
        // Prepare the response
        return response()->json([
            'page' => $myBusinessTrip->currentPage(),
            'pageCount' => $myBusinessTrip->lastPage(),
            'sortField' => 'created_at',
            'sortOrder' => 'desc',
            'totalCount' => $myBusinessTrip->total(),
            'data' =>  $myBusinessTrip->items(),
        ]);
    }

    public function dataTableConfirmBusinessTrip(Request $request){

        Gate::authorize('approvePolicy', EmployeeBusinessTrip::class);

        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10

        $myBusinessTrip = EmployeeBusinessTrip::with('employee')->where('known_by',Auth::user()->employee_id)
        ->orderBy('created_at', 'desc')
        ->paginate($pageSize);
                       // Prepare the response
        return response()->json([
            'page' => $myBusinessTrip->currentPage(),
            'pageCount' => $myBusinessTrip->lastPage(),
            'sortField' => 'created_at',
            'sortOrder' => 'desc',
            'totalCount' => $myBusinessTrip->total(),
            'data' =>  $myBusinessTrip->items(),
        ]);
    }

}
