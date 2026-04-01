<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Services\RoleService;
use App\Http\Controllers\MasterController;
use App\Models\Role;
use App\Services\PermissionGroupService;
use Illuminate\Support\Facades\Gate;


class RoleController extends MasterController
{
    protected $roleService;
    protected $permissionGroupService;


    // Inject multiple services through the constructor
    public function __construct(RoleService $roleService, PermissionGroupService $permissionGroupService)
    {
        parent::__construct();
        $this->roleService = $roleService;
        $this->permissionGroupService = $permissionGroupService;

    }

    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', Role::class);

            $breadcrumbs = ['Roles & Permissions', 'Roles'];
            $pageTitle = "Roles";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('role-permission.role.index'));
    }


    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', Role::class);

            $breadcrumbs = ['Roles & Permissions', 'Roles', 'Tambah Role'];
            $pageTitle = "Tambah Role";
            $permissionGroupWithPermissions = $this->permissionGroupService->getAllPermissionGroupWithPermissions();
            $this->data = compact('breadcrumbs', 'pageTitle', 'permissionGroupWithPermissions');
        };

        return $this->callFunction($func, view('role-permission.role.create'));
    }

    public function store(Request $request)
    {
        $func = function () use ($request){
            Gate::authorize('createPolicy', Role::class);


            $data = $request->validate([
                'permissions' => 'array',
                'name' => 'required|string',
                'is_active'=> 'required|string'
            ]);


            $this->roleService->storeRole($data);
            $this->messages = ['Role baru berhasil ditambahkan!'];
        };

        return $this->callFunction($func, null, 'roles.index');
    }

    public function show($id)
    {
        $func = function () use ($id){
            Gate::authorize('readPolicy', Role::class);


            $breadcrumbs = ['Roles & Permissions', 'Roles', 'Lihat Role'];
            $pageTitle = "Lihat Role";
            $editPageTitle = "Ubah Role";

            $permissionGroupWithPermissions = $this->permissionGroupService->getAllPermissionGroupWithPermissions();
            $role = $this->roleService->showRole($id);

            $this->data = compact('breadcrumbs', 'pageTitle', 'editPageTitle', 'permissionGroupWithPermissions','role');

       };

       return $this->callFunction($func, view('role-permission.role.show'));

    }

    public function edit ($id){
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', Role::class);

            $breadcrumbs = ['Roles & Permissions', 'Roles', 'Ubah Role'];
            $pageTitle = "Ubah Role";

            $permissionGroupWithPermissions = $this->permissionGroupService->getAllPermissionGroupWithPermissions();
            $role = $this->roleService->showRole($id);

            $this->data = compact('breadcrumbs', 'pageTitle', 'permissionGroupWithPermissions','role');
        };

        return $this->callFunction($func, view('role-permission.role.edit'));
    }

    public function update($id, Request $request)
    {
        $func = function () use ($id, $request) {
            Gate::authorize('updatePolicy', Role::class);

            $data = $request->validate([
                'permissions' => 'array',
                'name' => 'required|string',
                'is_active'=> 'required|string'
            ]);

            $this->roleService->updateRole($id,$data);
            session()->flash('flashMessageSuccess', 'Task was successful!');


        };

        return $this->callFunction($func, null, 'roles.index');
    }
}
