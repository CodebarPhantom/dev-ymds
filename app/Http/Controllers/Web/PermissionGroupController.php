<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\MasterController;
use App\Models\PermissionGroup;
use App\Services\PermissionGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class PermissionGroupController extends MasterController
{
    protected $permissionGroupService;

    // Inject multiple services through the constructor
    public function __construct(PermissionGroupService $permissionGroupService)
    {
        parent::__construct();
        $this->permissionGroupService = $permissionGroupService;
    }

    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', PermissionGroup::class);

            $breadcrumbs = ['Roles & Permissions', 'Grup Hak Akses'];
            $pageTitle = "Grup Hak Akses";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('role-permission.permission-group.index'));
    }


    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', PermissionGroup::class);

            $breadcrumbs = ['Roles & Permissions', 'Grup Hak Akses', 'Tambah Grup Hak Akses'];
            $pageTitle = "Tambah Grup Hak Akses";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('role-permission.permission-group.create'));
    }

    public function store(Request $request)
    {
        $func = function () use ($request) {

            Gate::authorize('createPolicy', PermissionGroup::class);


            $data = $request->validate([
                'name' => 'required|string',
                'is_active' => 'required|boolean|'
            ]);

            $this->permissionGroupService->storePermissionGroup($data);
            $this->messages = ['Grup Hak Akses berhasil ditambahkan!'];
        };

        return $this->callFunction($func, null, 'permission-groups.index');
    }

    public function show($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', PermissionGroup::class);

            $breadcrumbs = ['Roles & Permissions', 'Grup Hak Akses', 'Lihat Grup Hak Akses'];
            $pageTitle = "Lihat Grup Hak Akses";
            $editPageTitle = "Ubah Grup Hak Akses";

            $permissionGroup = $this->permissionGroupService->showPermissionGroup($id);
            $this->data = compact('breadcrumbs', 'pageTitle', 'editPageTitle', 'permissionGroup');
        };

        return $this->callFunction($func, view('role-permission.permission-group.show'));
    }



    public function edit ($id){
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', PermissionGroup::class);

            $breadcrumbs = ['Roles & Permissions', 'Grup Hak Akses', 'Ubah Grup Hak Akses'];
            $pageTitle = "Ubah Grup Hak Akses";

            $permissionGroup = $this->permissionGroupService->showPermissionGroup($id);
            $this->data = compact('breadcrumbs', 'pageTitle', 'permissionGroup');
        };

        return $this->callFunction($func, view('role-permission.permission-group.edit'));
    }

    public function update($id, Request $request)
    {
        $func = function () use ($id, $request) {
            Gate::authorize('updatePolicy', PermissionGroup::class);

            $data = $request->validate([
                'name' => 'required|string',
                'is_active' => 'required|boolean|'
            ]);

            $this->permissionGroupService->updatePermissionGroup($id,$data);
            $this->messages = ['Grup Hak Akses berhasil diubah!'];


        };

        return $this->callFunction($func, null, 'permission-groups.index');
    }
}
