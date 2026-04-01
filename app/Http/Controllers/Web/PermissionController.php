<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\MasterController;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Services\PermissionGroupService;
use App\Services\PermissionService;

use Illuminate\Support\Facades\Gate;
use App\Models\User;

class PermissionController extends MasterController
{
    protected $permissionService;
    protected $permissionGroupService;


    // Inject multiple services through the constructor
    public function __construct(PermissionService $permissionService, PermissionGroupService $permissionGroupService)
    {
        parent::__construct();
        $this->permissionService = $permissionService;
        $this->permissionGroupService = $permissionGroupService;

    }

    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', Permission::class);

            $breadcrumbs = ['Hak Akses'];
            $pageTitle = "Hak Akses";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('role-permission.permission.index'));
    }


    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', Permission::class);

            $breadcrumbs = ['Hak Akses', 'Tambah Hak Akses'];
            $pageTitle = "Tambah Hak Akses";
            $permissionGroups = $this->permissionGroupService->getAllPermissionGroups();
            $this->data = compact('breadcrumbs', 'pageTitle', 'permissionGroups');
        };

        return $this->callFunction($func, view('role-permission.permission.create'));
    }

    public function store(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('createPolicy', Permission::class);


            $data = $request->validate([
                'permission_group_id' => 'required|string',
                'name' => 'required|string'
            ]);

            $this->permissionService->storePermission($data);
            $this->messages = ['Permission berhasil ditambahkan!'];
        };

        return $this->callFunction($func, null, 'permissions.index');
    }

    public function show($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', Permission::class);

            $breadcrumbs = ['Hak Akses', 'Lihat Hak Akses'];
            $pageTitle = "Lihat Hak Akses";
            $editPageTitle = "Ubah Hak Akses";
            $permissionGroups = $this->permissionGroupService->getAllPermissionGroups();
            $permission = $this->permissionService->showPermission($id);

            $this->data = compact('breadcrumbs', 'pageTitle', 'editPageTitle', 'permission', 'permissionGroups');
        };

        return $this->callFunction($func, view('role-permission.permission.show'));
    }

    public function edit ($id){
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', Permission::class);

            $breadcrumbs = ['Hak Akses', 'Ubah Hak Akses'];
            $pageTitle = "Ubah Hak Akses";

            $permission = $this->permissionService->showPermission($id);
            $permissionGroups = $this->permissionGroupService->getAllPermissionGroups();

            $this->data = compact('breadcrumbs', 'pageTitle', 'permission', 'permissionGroups');
        };

        return $this->callFunction($func, view('role-permission.permission.edit'));
    }

    public function update($id, Request $request)
    {
        $func = function () use ($id, $request) {
            Gate::authorize('updatePolicy', Permission::class);

            $data = $request->validate([
                'permission_group_id' => 'required|string',
                'name' => 'required|string'
            ]);

            $this->permissionService->updatePermission($id,$data);
            $this->messages = ['Permission berhasil diubah!'];



        };

        return $this->callFunction($func, null, 'permissions.index');
    }
}
