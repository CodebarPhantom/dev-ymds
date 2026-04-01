<?php

namespace App\Http\Controllers\Web\WorkSchedule;

use App\Services\WorkSchedule\ShiftFixedService;
use App\Http\Controllers\MasterController;
use App\Services\WorkSchedule\ShiftService;
use App\Services\RoleService;
use Illuminate\Support\Facades\Gate;
use App\Models\WorkSchedule\Shift;
use Illuminate\Http\Request;

class ShiftFixedController extends MasterController
{

    protected $shiftFixedService;
    protected $shiftService;
    protected $roleService;



    // Inject multiple services through the constructor
    public function __construct(ShiftService $shiftService, ShiftFixedService $shiftFixedService, RoleService $roleService)
    {
        parent::__construct();
        $this->shiftFixedService = $shiftFixedService;
        $this->roleService = $roleService;
        $this->shiftService = $shiftService;

    }
     /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', Shift::class);

            $breadcrumbs = ['Jadwal Kerja','Kelola Shift', 'Shift Tetap'];
            $pageTitle = "Shift Tetap";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('backoffice.work-schedule.shift-fixed.index'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', Shift::class);

            $breadcrumbs = ['Jadwal Kerja','Kelola Shift', 'Shift Tetap', 'Tambah Shift Tetap'];
            $pageTitle = "Tambah Shift Tetap";
            //$roles = $this->roleService->getAllRole();
            $shifts = $this->shiftService->getAllShift();
            $this->data = compact('breadcrumbs', 'pageTitle','shifts');
        };

        return $this->callFunction($func, view('backoffice.work-schedule.shift-fixed.create'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('createPolicy', Shift::class);
            $request->merge([
                'roles' => json_decode($request->input('roles', '[]'), true),
            ]);
            $data = $request->validate([
                'roles' => 'required|array',
                'shift_id' => 'required|integer',
                'day_off.*' => 'required',
                'name' => 'required',
            ]);

            $this->shiftFixedService->createShiftFixed($data);
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };

        return $this->callFunction($func, null, 'work-schedule.shift-fixed.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', Shift::class);

            $breadcrumbs = ['Jadwal Kerja','Kelola Shift', 'Shift Tetap', 'Lihat Shift Tetap'];
            $pageTitle = "Lihat Shift Tetap";
            $editPageTitle = "Ubah Shift Tetap";

            $shiftFixed = $this->shiftFixedService->getShiftFixedById($id);
            $roleShiftIds = $shiftFixed->rolesShiftFixed->map(function ($roleShiftFixed) {
                return $roleShiftFixed->role_id;
            })->toArray();
            $roleShiftIdsString = '[' . implode(',', $roleShiftIds) . ']';
            $shifts = $this->shiftService->getAllShift();
            $this->data = compact('breadcrumbs', 'pageTitle', 'editPageTitle', 'shiftFixed', 'shifts', 'roleShiftIdsString');
        };

        return $this->callFunction($func, view('backoffice.work-schedule.shift-fixed.show'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', Shift::class);


            $breadcrumbs = ['Jadwal Kerja','Kelola Shift', 'Shift Tetap', 'Ubah Shift Tetap'];
            $pageTitle = "Ubah Shift Tetap";

            $shiftFixed = $this->shiftFixedService->getShiftFixedById($id);
            $roleShiftIds = $shiftFixed->rolesShiftFixed->map(function ($roleShiftFixed) {
                return $roleShiftFixed->role_id;
            })->toArray();
            $roleShiftIdsString = '[' . implode(',', $roleShiftIds) . ']';
            $shifts = $this->shiftService->getAllShift();
            $this->data = compact('breadcrumbs', 'pageTitle', 'shiftFixed', 'roleShiftIdsString', 'shifts');
        };

        return $this->callFunction($func, view('backoffice.work-schedule.shift-fixed.edit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $func = function () use ($id, $request) {
            Gate::authorize('updatePolicy', Shift::class);

            $request->merge([
                'roles' => json_decode($request->input('roles', '[]'), true),
            ]);

            $data = $request->validate([
                'roles' => 'required|array',
                'shift_id' => 'required|integer',
                'day_off.*' => 'required',
                'name' => 'required',
            ]);

            $this->shiftFixedService->updateShiftFixed($id,$data);
            session()->flash('flashMessageSuccess', 'Task was successful!');


        };

        return $this->callFunction($func, null, 'work-schedule.shift-fixed.index');
    }
}
