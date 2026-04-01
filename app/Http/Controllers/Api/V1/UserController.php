<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\MasterController;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class UserController extends MasterController
{
    protected $userService;

    // Inject multiple services through the constructor
    public function __construct(UserService $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

     public function dataTable( Request $request){
        Gate::authorize('readPolicy', User::class);

        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10
        $sortField = $request->input('sortField', 'name'); // Default sort field
        $sortOrder = $request->input('sortOrder', 'asc'); // Default sort order

        // Validate sort field and order
        $allowedSortFields = ['name']; // Add your sortable fields
        $allowedSortOrders = ['asc', 'desc'];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'name'; // Fallback to default
        }

        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'asc'; // Fallback to default
        }
        $users = User::with(['location','role'])->where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->whereRaw('name ILIKE ?', ['%'.$search.'%']);
            }
        })
        ->orderBy($sortField, $sortOrder)
        ->paginate($pageSize);

        // Log::debug('UserController dataTable', [
        //     'search' => $search,
        //     'pageSize' => $pageSize,
        //     'sortField' => $sortField,
        //     'sortOrder' => $sortOrder,
        //     'totalCount' => $users,
        // ]);

        $userData = $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active'=> $user->is_active,
                'is_active_name'=> $user->is_active_name,
                'is_active_color' => $user->is_active_color, // Accessor used here
                'role' => $user->role->name
            ];
        });


                       // Prepare the response
        return response()->json([
            'page' => $users->currentPage(),
            'pageCount' => $users->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $users->total(),
            'data' =>  $userData,
        ]);
    }

    public function getCombobox(Request $request)
    {
        $func = function () use ($request) {
            //Gate::authorize('readPolicy', Departement::class);

            $locationId = $request->input('location_id');
            $locations = $this->userService->getAllUserForSelect($locationId);

            $this->data = compact('locations');
        };

        return $this->callFunction($func);
    }

    // public function create(Request $request)
    // {
    //     $func = function () use ($request) {

    //        Gate::authorize('create', User::class); // is from policy

    //         $data = $request->validate([
    //             'name'=> 'required|string',
    //             'email' => 'required|string|email',
    //             'password' => 'required|string'
    //         ]);

    //         $user = $this->userService->createUser($data);

    //         // Set the data and messages
    //         $this->data = $user;
    //     };

    //     return $this->callFunction($func, null, null);
    // }

}
