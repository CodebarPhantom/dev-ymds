<?php

namespace App\Http\Controllers\Web\WorkSchedule;

use App\Http\Controllers\MasterController;
use App\Models\WorkSchedule\Holiday;
use Illuminate\Http\Request;
use App\Services\WorkSchedule\HolidayService;
use Illuminate\Support\Facades\Gate;


class HolidayController extends MasterController
{

    protected $holidayService;

    // Inject multiple services through the constructor
    public function __construct(HolidayService $holidayService)
    {
        parent::__construct();
        $this->holidayService = $holidayService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', Holiday::class);

            $breadcrumbs = ['Jadwal Kerja', 'Hari Libur'];
            $pageTitle = "Hari Libur";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('backoffice.work-schedule.holiday.index'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', Holiday::class);

            $breadcrumbs = ['Jadwal Kerja', 'Hari Libur', 'Tambah Hari Libur'];
            $pageTitle = "Tambah Hari Libur";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('backoffice.work-schedule.holiday.create'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('createPolicy', Holiday::class);
            $request->merge([
                'roles' => json_decode($request->input('roles', '[]'), true),
            ]);
            $data = $request->validate([
                'name' => 'required|string',
                'date' => 'required|date|',
                'roles' => 'required|array',
                //'applies_to_shift'=>'boolean'
            ]);

            $this->holidayService->createHoliday($data);
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };

        return $this->callFunction($func, null, 'work-schedule.holiday.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', Holiday::class);

            $breadcrumbs = ['Jadwal Kerja', 'Hari Libur', 'Lihat Hari Libur'];
            $pageTitle = "Lihat Hari Libur";
            $editPageTitle = "Ubah Hari Libur";

            $holiday = $this->holidayService->getHolidayById($id);
            $holidayShiftIds = $holiday->holidayRoles->map(function ($holidayRole) {
                return $holidayRole->role_id;
            })->toArray();
            $holidayRoleIdsString = '[' . implode(',', $holidayShiftIds) . ']';


            $this->data = compact('breadcrumbs', 'pageTitle', 'editPageTitle', 'holiday', 'holidayRoleIdsString');
        };

        return $this->callFunction($func, view('backoffice.work-schedule.holiday.show'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $func = function () use ($id) {
            Gate::authorize('updatePolicy', Holiday::class);


            $breadcrumbs = ['Jadwal Kerja', 'Hari Libur', 'Ubah Hari Libur'];
            $pageTitle = "Ubah Hari Libur";

            $holiday = $this->holidayService->getHolidayById($id);
            $holidayShiftIds = $holiday->holidayRoles->map(function ($holidayRole) {
                return $holidayRole->role_id;
            })->toArray();
            $holidayRoleIdsString = '[' . implode(',', $holidayShiftIds) . ']';

            $this->data = compact('breadcrumbs', 'pageTitle', 'holiday', 'holidayRoleIdsString');
        };

        return $this->callFunction($func, view('backoffice.work-schedule.holiday.edit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $func = function () use ($id, $request) {
            Gate::authorize('updatePolicy', Holiday::class);

            $request->merge([
                'roles' => json_decode($request->input('roles', '[]'), true),
            ]);

            $data = $request->validate([
                'name' => 'required|string',
                'date' => 'required|date|',
                'roles' => 'required|array',

            ]);

            $this->holidayService->updateHoliday($id,$data);
            session()->flash('flashMessageSuccess', 'Task was successful!');


        };

        return $this->callFunction($func, null, 'work-schedule.holiday.index');
    }

}
