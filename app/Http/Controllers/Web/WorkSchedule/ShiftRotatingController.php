<?php

namespace App\Http\Controllers\Web\WorkSchedule;

use App\Services\WorkSchedule\ShiftRotatingService;
use App\Http\Controllers\MasterController;
use App\Services\WorkSchedule\ShiftService;
use App\Services\WorkSchedule\ShiftFixedService;
use App\Services\Workforce\EmployeeService;
use Illuminate\Support\Facades\Gate;
use App\Models\WorkSchedule\Shift;
use Illuminate\Http\Request;

class ShiftRotatingController extends MasterController
{

    protected $shiftService;
    protected $employeeService;
    protected $shiftFixedService;
    protected $shiftRotatingService;

    // Inject multiple services through the constructor
    public function __construct(
        ShiftService $shiftService,
        ShiftRotatingService $shiftRotatingService,
        ShiftFixedService $shiftFixedService,
        EmployeeService $employeeService
    )
    {
        parent::__construct();
        $this->shiftFixedService = $shiftFixedService;
        $this->shiftRotatingService = $shiftRotatingService;
        $this->employeeService = $employeeService;
        $this->shiftService = $shiftService;

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', Shift::class);

            $breadcrumbs = ['Jadwal Kerja','Kelola Shift', 'Shift Bergilir'];
            $pageTitle = "Shift Bergilir";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('backoffice.work-schedule.shift-rotating.index'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', Shift::class);

            $breadcrumbs = ['Jadwal Kerja','Kelola Shift', 'Shift Tetap', 'Tambah Shift Bergilir'];
            $pageTitle = "Tambah Shift Bergilir";
            //$shiftFixedRoleIdArray = $this->shiftFixedService->getAllShiftFixed()->pluck('role_id')->toArray();
            //$employees = $this->employeeService->getAllEmployeeWithExcludeRole($shiftFixedRoleIdArray);
            $shifts = $this->shiftService->getAllShift();
            $this->data = compact('breadcrumbs', 'pageTitle','shifts');
        };

        return $this->callFunction($func, view('backoffice.work-schedule.shift-rotating.create'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('createPolicy', Shift::class);
            $request->merge([
                'shift_employees' => json_decode($request->input('shift_employees', '[]'), true),
            ]);

            $data = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'shift_employees' =>'required|array',
                'shift_id' => 'required|integer',
                'is_dayoff' => 'required|boolean'
            ]);

            $this->shiftRotatingService->createShiftRotating($data);
            $this->messages = ['Shift bergilir berhasil dibuat tinggal menunggu waktu untuk generate'];
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };

        return $this->callFunction($func, null, 'work-schedule.shift-rotating.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', Shift::class);

            $breadcrumbs = ['Jadwal Kerja','Kelola Shift', 'Shift Bergilir', 'Lihat Shift Bergilir'];
            $pageTitle = "Lihat Shift Bergilir";
            $editPageTitle = "Lihats Shift Bergilir";

            $shiftRotating = $this->shiftRotatingService->getShiftRotatingById($id);
            $employeeShiftIds = $shiftRotating->employeeShifts->map(function ($employeeShift) {
                return $employeeShift->employee_id;
            })->toArray();
            $employeeShiftIdsString = '[' . implode(',', $employeeShiftIds) . ']';
            $shifts = $this->shiftService->getAllShift();
            $this->data = compact('breadcrumbs', 'pageTitle', 'editPageTitle', 'shiftRotating', 'employeeShiftIdsString', 'shifts');


        };

        return $this->callFunction($func, view('backoffice.work-schedule.shift-rotating.show'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', Shift::class);

            $breadcrumbs = ['Jadwal Kerja','Kelola Shift', 'Shift Bergilir', 'Ubah Shift Bergilir'];
            $pageTitle = "Ubah Shift Bergilir";
            $editPageTitle = "Ubah Shift Bergilir";

            $shiftRotating = $this->shiftRotatingService->getShiftRotatingById($id);
            $employeeShiftIds = $shiftRotating->employeeShifts->map(function ($employeeShift) {
                return $employeeShift->employee_id;
            })->toArray();
            $employeeShiftIdsString = '[' . implode(',', $employeeShiftIds) . ']';
            $shifts = $this->shiftService->getAllShift();
            $this->data = compact('breadcrumbs', 'pageTitle', 'editPageTitle', 'shiftRotating', 'employeeShiftIdsString', 'shifts');


        };

        return $this->callFunction($func, view('backoffice.work-schedule.shift-rotating.edit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $func = function () use ($id, $request) {
            Gate::authorize('updatePolicy', Shift::class);

            $request->merge([
                'shift_employees' => json_decode($request->input('shift_employees', '[]'), true),
            ]);


            $data = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'shift_employees' =>'required|array',
                'shift_id' => 'required|integer',
                'is_dayoff' => 'required|boolean'
            ]);

            $this->shiftRotatingService->updateShiftRotating($id,$data);
            session()->flash('flashMessageSuccess', 'Task was successful!');


        };

        return $this->callFunction($func, null, 'work-schedule.shift-rotating.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShiftRotating $shiftRotating)
    {
        //
    }
}
