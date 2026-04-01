<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\MasterController;
use App\Models\Entity;
use Illuminate\Http\Request;
use App\Services\EntityService;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class EntityController extends MasterController
{
    protected $entityService;

    // Inject multiple services through the constructor
    public function __construct(EntityService $entityService)
    {
        parent::__construct();
        $this->entityService = $entityService;
    }

    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', Entity::class);

            $breadcrumbs = ['Perusahaan', 'Entitas'];
            $pageTitle = "Entitas";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('backoffice.config.company.entity.index'));
    }


    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', Entity::class);

            $breadcrumbs = ['Perusahaan', 'Entitas', 'Tambah Entitas'];
            $pageTitle = "Tambah Entitas";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('backoffice.config.company.entity.create'));
    }

    public function store(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('createPolicy', Entity::class);



            $data = $request->validate([
                'name' => 'required|string',
                'status' => 'required|boolean|'
            ]);

            $this->entityService->storeEntity($data);
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };

        return $this->callFunction($func, null, 'entity.index');
    }

    public function show($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', Entity::class);

            $breadcrumbs = ['Perusahaan', 'Entitas', 'Lihat Entitas'];
            $pageTitle = "Lihat Entitas";
            $editPageTitle = "Ubah Entitas";

            $entity = $this->entityService->showEntity($id);
            $this->data = compact('breadcrumbs', 'pageTitle', 'editPageTitle', 'entity');
        };

        return $this->callFunction($func, view('backoffice.config.company.entity.show'));
    }



    public function edit ($id){
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', Entity::class);


            $breadcrumbs = ['Perusahaan', 'Entitas', 'Ubah Entitas'];
            $pageTitle = "Ubah Entitas";

            $entity = $this->entityService->showEntity($id);
            $this->data = compact('breadcrumbs', 'pageTitle', 'entity');
        };

        return $this->callFunction($func, view('backoffice.config.company.entity.edit'));
    }

    public function update($id, Request $request)
    {
        $func = function () use ($id, $request) {
            Gate::authorize('updatePolicy', Entity::class);

            $data = $request->validate([
                'name' => 'required|string',
                'status' => 'required|boolean|'
            ]);

            $this->entityService->updateEntity($id,$data);
            session()->flash('flashMessageSuccess', 'Task was successful!');


        };

        return $this->callFunction($func, null, 'entity.index');
    }
}
