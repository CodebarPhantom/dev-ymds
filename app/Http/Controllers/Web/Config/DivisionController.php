<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\MasterController;
use App\Models\Division;
use App\Services\DivisionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class DivisionController extends MasterController
{
    protected $divisionService;

    // Inject multiple services through the constructor
    public function __construct(DivisionService $divisionService)
    {
        parent::__construct();
        $this->divisionService = $divisionService;
    }

    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', Division::class);

            $breadcrumbs = ['Perusahaan', 'Divisi'];
            $pageTitle = "Divisi";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('backoffice.config.company.division.index'));
    }


    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', Division::class);

            $breadcrumbs = ['Perusahaan', 'Divisi', 'Tambah Divisi'];
            $pageTitle = "Tambah Divisi";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('backoffice.config.company.division.create'));
    }

    public function store(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('createPolicy', Division::class);

            $data = $request->validate([
                'name' => 'required|string',
                'is_active' => 'required|boolean|'
            ]);

            $this->divisionService->storeDivision($data);
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };

        return $this->callFunction($func, null, 'division.index');
    }

    public function show($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', Division::class);

            $breadcrumbs = ['Perusahaan', 'Divisi', 'Lihat Divisi'];
            $pageTitle = "Lihat Divisi";
            $editPageTitle = "Ubah Divisi";

            $division = $this->divisionService->showDivision($id);
            $this->data = compact('breadcrumbs', 'pageTitle', 'editPageTitle', 'division');
        };

        return $this->callFunction($func, view('backoffice.config.company.division.show'));
    }



    public function edit ($id){
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', Division::class);

            $breadcrumbs = ['Perusahaan', 'Divisi', 'Ubah Divisi'];
            $pageTitle = "Ubah Divisi";

            $division = $this->divisionService->showDivision($id);
            $this->data = compact('breadcrumbs', 'pageTitle', 'division');
        };

        return $this->callFunction($func, view('backoffice.config.company.division.edit'));
    }

    public function update($id, Request $request)
    {
        $func = function () use ($id, $request) {
            Gate::authorize('updatePolicy', Division::class);

            $data = $request->validate([
                'name' => 'required|string',
                'is_active' => 'required|boolean|'
            ]);

            $this->divisionService->updateDivision($id,$data);
            session()->flash('flashMessageSuccess', 'Task was successful!');


        };

        return $this->callFunction($func, null, 'division.index');
    }
}
