<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\MasterController;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Services\LocationService;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class LocationController extends MasterController
{
    protected $locationService;


    // Inject multiple services through the constructor
    public function __construct(LocationService $locationService)
    {
        parent::__construct();
        $this->locationService = $locationService;

    }

    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', Location::class); // is from policy

            $breadcrumbs = ['Lokasi'];
            $pageTitle = "Lokasi";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('location.index'));
    }


    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', Location::class);

            $breadcrumbs = ['Lokasi', 'Tambah Lokasi'];
            $pageTitle = "Tambah Lokasi";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('location.create'));
    }

    public function store(Request $request)
    {
        $func = function () use ($request) {

            Gate::authorize('createPolicy', Location::class);

            $data = $request->validate([
                'name' => 'required|string',
                'address' => 'required|string|',
                'phone' => 'required|string|',
                'status' => 'required|boolean|',
                'longitude' => 'required|string|',
                'latitude' => 'required|string|',
                //'radius' => 'required|integer|',
            ]);

            $this->locationService->storeLocation($data);
            $this->messages = ['Lokasi berhasil ditambahkan!'];
        };

        return $this->callFunction($func, null, 'location.index');
    }

    public function show($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', Location::class); // is from policy

            $breadcrumbs = ['Lokasi', 'Lihat Lokasi'];
            $pageTitle = "Lihat Lokasi";
            $editPageTitle = "Ubah Lokasi";
            $location = $this->locationService->showLocation($id);

            $this->data = compact('breadcrumbs', 'pageTitle', 'editPageTitle', 'location');
        };

        return $this->callFunction($func, view('location.show'));
    }



    public function edit ($id){
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', Location::class);


            $breadcrumbs = ['Lokasi', 'Ubah Lokasi'];
            $pageTitle = "Ubah Lokasi";

            $location = $this->locationService->showLocation($id);

            $this->data = compact('breadcrumbs', 'pageTitle', 'location');
        };

        return $this->callFunction($func, view('location.edit'));
    }

    public function update($id, Request $request)
    {
        $func = function () use ($id, $request) {
            Gate::authorize('updatePolicy', Location::class);

            $data = $request->validate([

                'name' => 'required|string',
                'address' => 'required|string|',
                'phone' => 'required|string|',
                'status' => 'required|boolean|',
                'longitude' => 'required|string|',
                'latitude' => 'required|string|',
            ]);

            $this->locationService->updateLocation($id,$data);
            $this->messages = ['Lokasi berhasil diubah!'];
        };

        return $this->callFunction($func, null, 'location.index');
    }
}
