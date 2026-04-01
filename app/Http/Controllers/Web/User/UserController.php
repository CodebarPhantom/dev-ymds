<?php

namespace App\Http\Controllers\Web\User;

use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\LocationService;
use App\Http\Controllers\MasterController;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Services\RoleService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends MasterController
{
    protected $userService, $locationService, $roleService;

    // Inject multiple services through the constructor
    public function __construct(
        UserService $userService,
        LocationService $locationService,
        RoleService $roleService
    ) {
        parent::__construct();
        $this->userService = $userService;
        $this->locationService = $locationService;
        $this->roleService = $roleService;
    }

    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', User::class); // is from policy

            $breadcrumbs = ['User'];
            $pageTitle = "User";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('user.index'));
    }

    public function create(Request $request)
    {
        $func = function () use ($request) {

            Gate::authorize('createPolicy', User::class);
            $breadcrumbs = ['User', 'Tambah User'];
            $pageTitle = "Tambah User";
            $locations = $this->locationService->getAllLocations();
            $roles = $this->roleService->getAllRole();
            $this->data = compact('breadcrumbs', 'pageTitle', 'locations', 'roles');
        };

        return $this->callFunction($func, view('user.create'), null);
    }

    public function store(Request $request)
    {
        $func = function () use ($request) {

            Gate::authorize('createPolicy', User::class);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'location_id' => 'required|',
                'password' => 'required|string|min:4|confirmed',
                'role_id' => 'required|exists:roles,id',
                //'url_image' => 'nullable|image|max:2048', // max 2MB
            ]);

            //dd($validated);

            // Handle password kosong
            // if (empty($validated['password'])) {
            //     unset($validated['password']); // Jangan kirim password kalau kosong
            // }

            // Handle upload file gambar (jika ada)
            // if ($request->hasFile('url_image')) {
            //     $file = $request->file('url_image');
            //     $path = $file->store('uploads/profile_images', 'public');
            //     $validated['url_image'] = 'storage/' . $path;
            // }

            // Simpan data ke database lewat service layer
            $user = $this->userService->createUser($validated);
            $this->messages = ['User berhasil ditambahkan!'];
            session()->flash('flashMessageSuccess');
            //$this->data = $user;
        };

        return $this->callFunction($func, null, 'users.index');
    }

    public function show($id)
    {
        $func = function ()  use ($id) {
            Gate::authorize('readPolicy', User::class);
            $breadcrumbs = ['User', 'Lihat User'];
            $pageTitle = "Lihat User";
            $user = $this->userService->showUser($id);
            $editPageTitle = "Ubah User";
            $locations = $this->locationService->getAllLocations();
            $roles = $this->roleService->getAllRole();


            $this->data = compact('breadcrumbs', 'pageTitle', 'editPageTitle', 'user', 'locations', 'roles');
        };
        return $this->callFunction($func, view('user.show'), null);
    }

    public function edit($id)
    {
        $func = function ()  use ($id) {
            Gate::authorize('updatePolicy', User::class);
            $breadcrumbs = ['User', 'Ubah User'];
            $pageTitle = "Ubah User";
            $user = $this->userService->showUser($id);
            //$locations = $this->locationService->getAllLocations();
            $roles = $this->roleService->getAllRole();

            $this->data = compact('breadcrumbs', 'pageTitle', 'user', 'roles');
        };
        return $this->callFunction($func, view('user.edit'), null);
    }

    // Controller: UserController.php

    public function update($id, Request $request)
    {
        $func = function () use ($id, $request) {

            // Cek policy (misal update)
            Gate::authorize('updatePolicy', User::class);

            // Validasi request
            $validated = $request->validate([
                'name'                  => 'required|string|max:255',
                'email'                 => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($id),
                ],
                'location_id'           => 'required',
                'role_id'               => 'required|exists:roles,id',
                'password'              => 'nullable|string|min:4|confirmed',
                'is_active'             => 'required|boolean',
            ]);

            // Kalau password dikosongkan, jangan kirim ke service
            if (empty($validated['password'])) {
                unset($validated['password']);
            }

            // Panggil service layer
            $user = $this->userService->updateUser($id, $validated);

            // Siapkan data untuk dipakai di view / response
            $this->data = $user;
        };

        // Setelah selesai, redirect ke route users.index
        return $this->callFunction($func, null, 'users.index');
    }
}
