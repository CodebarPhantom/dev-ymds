<?php

namespace App\Http\Controllers\Web\WorkSchedule;

use App\Models\WorkSchedule\Shift;
use App\Services\WorkSchedule\ShiftService;
use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use Illuminate\Support\Facades\Gate;


class ShiftController extends MasterController
{

    protected $shiftService;

    // Inject multiple services through the constructor
    public function __construct(ShiftService $shiftService)
    {
        parent::__construct();
        $this->shiftService = $shiftService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', Shift::class);

            $breadcrumbs = ['Jadwal Kerja','Kelola Shift', 'Shift'];
            $pageTitle = "Shift";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('backoffice.work-schedule.shift.index'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', Shift::class);

            $breadcrumbs = ['Jadwal Kerja','Kelola Shift', 'Shift', 'Tambah Shift'];
            $pageTitle = "Tambah Shift";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('backoffice.work-schedule.shift.create'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('createPolicy', Shift::class);
            $data = $request->validate([
                'name' => 'required|string',
                'start_time' => 'required',
                'end_time' => 'required',
                'is_night_shift'=>'boolean',
                'duration_hours' => 'required|integer',
            ]);

            $this->shiftService->createShift($data);
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };

        return $this->callFunction($func, null, 'work-schedule.shift.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', Shift::class);

            $breadcrumbs = ['Jadwal Kerja','Kelola Shift', 'Shift', 'Lihat Shift'];
            $pageTitle = "Lihat Shift";
            $editPageTitle = "Ubah Shift";

            $shift = $this->shiftService->getShiftById($id);
            $this->data = compact('breadcrumbs', 'pageTitle', 'editPageTitle', 'shift');
        };

        return $this->callFunction($func, view('backoffice.work-schedule.shift.show'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', Shift::class);


            $breadcrumbs = ['Jadwal Kerja','Kelola Shift', 'Shift', 'Ubah Shift'];
            $pageTitle = "Ubah Shift";

            $shift = $this->shiftService->getShiftById($id);
            $this->data = compact('breadcrumbs', 'pageTitle', 'shift');
        };

        return $this->callFunction($func, view('backoffice.work-schedule.shift.edit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $func = function () use ($id, $request) {
            Gate::authorize('updatePolicy', Shift::class);

            $data = $request->validate([
                'name' => 'required|string',
                'start_time' => 'required',
                'end_time' => 'required',
                'is_night_shift'=>'boolean',
                'duration_hours' => 'required|integer',
            ]);

            $this->shiftService->updateShift($id,$data);
            session()->flash('flashMessageSuccess', 'Task was successful!');


        };

        return $this->callFunction($func, null, 'work-schedule.shift.index');
    }

}
