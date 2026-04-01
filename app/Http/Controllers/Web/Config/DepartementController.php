<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\MasterController;
use Illuminate\Http\Request;
use App\Services\DepartementService;
use App\Services\DivisionService;
use App\Models\Departement;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class DepartementController extends MasterController
{
    protected $departementService;
    protected $divisionService;


    // Inject multiple services through the constructor
    public function __construct(DepartementService $departementService, DivisionService $divisionService)
    {
        parent::__construct();
        $this->departementService = $departementService;
        $this->divisionService = $divisionService;

    }

    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', Departement::class);
            $breadcrumbs = ['Perusahaan', 'Departemen'];
            $pageTitle = "Departemen";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('backoffice.config.company.departement.index'));
    }


    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', Departement::class);

            $breadcrumbs = ['Perusahaan', 'Departemen', 'Tambah Departemen'];
            $pageTitle = "Tambah Departemen";
            $divisions = $this->divisionService->getAllDivisions();
            $this->data = compact('breadcrumbs', 'pageTitle', 'divisions');
        };

        return $this->callFunction($func, view('backoffice.config.company.departement.create'));
    }

    public function store(Request $request)
    {
        $func = function () use ($request) {

            Gate::authorize('createPolicy', Departement::class);


            $data = $request->validate([
                'division_id' => 'required|string',
                'name' => 'required|string',
                'is_active' => 'required|boolean|'
            ]);

            $this->departementService->storeDepartement($data);
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };

        return $this->callFunction($func, null, 'departement.index');
    }

    public function show($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', Departement::class); // is from policy


            $breadcrumbs = ['Perusahaan', 'Departemen', 'Lihat Departemen'];
            $pageTitle = "Lihat Departemen";
            $editPageTitle = "Ubah Departemen";
            $divisions = $this->divisionService->getAllDivisions();
            $departement = $this->departementService->showDepartement($id);

            $this->data = compact('breadcrumbs', 'pageTitle', 'editPageTitle', 'departement', 'divisions');
        };

        return $this->callFunction($func, view('backoffice.config.company.departement.show'));
    }



    public function edit ($id){
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', Departement::class);

            $breadcrumbs = ['Perusahaan', 'Departemen', 'Ubah Departemen'];
            $pageTitle = "Ubah Departemen";

            $departement = $this->departementService->showDepartement($id);
            $divisions = $this->divisionService->getAllDivisions();

            $this->data = compact('breadcrumbs', 'pageTitle', 'departement', 'divisions');
        };

        return $this->callFunction($func, view('backoffice.config.company.departement.edit'));
    }

    public function update($id, Request $request)
    {
        $func = function () use ($id, $request) {
            Gate::authorize('updatePolicy', Departement::class);


            $data = $request->validate([
                'division_id' => 'required|string',
                'name' => 'required|string',
                'is_active' => 'required|boolean|'
            ]);

            $this->departementService->updateDepartement($id,$data);
            session()->flash('flashMessageSuccess', 'Task was successful!');


        };

        return $this->callFunction($func, null, 'departement.index');
    }
}
