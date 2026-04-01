<?php

namespace App\Http\Controllers\Web\MyActivity;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MasterController;
use App\Models\Workforce\EmployeeOvertime;
use App\Services\Workforce\EmployeeOvertimeService;
use App\Services\WorkSchedule\ShiftService;
use Illuminate\Support\Facades\Gate;

class MyOvertimeController extends MasterController
{

    protected $employeeOvertimeService;
    protected $shiftService;


    public function __construct(
        EmployeeOvertimeService $employeeOvertimeService,
        ShiftService $shiftService
    ) {
        parent::__construct();
        $this->employeeOvertimeService = $employeeOvertimeService;
        $this->shiftService = $shiftService;
    }

    public function index()
    {
        $func = function () {

            $breadcrumbs = ['Aktifitas Saya', 'Lembur'];
            $pageTitle = "Lembur";
            $flashMessageSuccess = session('flashMessageSuccess');
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess');
        };

        return $this->callFunction($func, view('backoffice.my-activity.overtime.index'));
    }

    public function create()
    {
        $func = function () {
            $breadcrumbs = ['Aktifitas Saya', 'Lembur', 'Buat Lembur'];
            $pageTitle = "Buat Lembur";
            $shifts =  $this->shiftService->getAllShift();
            $this->data = compact('breadcrumbs', 'pageTitle','shifts' );
        };

        return $this->callFunction($func, view('backoffice.my-activity.overtime.create'));
    }

    public function store(Request $request)
    {
        $func = function () use ($request) {


            $data = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'shift_id' => 'required|exists:shifts,id',
                'note' => 'nullable|string',
                'overtime_hour' => 'required|numeric'
            ]);


            $this->employeeOvertimeService->createOvertime($data);
            $this->messages = ['Pengajuan Lembur berhasil dibuat'];
            session()->flash('flashMessageSuccess');
        };

        return $this->callFunction($func, null, 'my-activity.my-overtime.index');
    }

    public function show($id)
    {
        $func = function () use($id) {
            $breadcrumbs = ['Aktifitas Saya', 'Lembur', 'Lihat Lembur'];
            $pageTitle = "Lihat Lembur";
            $overtime = $this->employeeOvertimeService->showOvertime($id);

            $this->data = compact('breadcrumbs', 'pageTitle', 'overtime');
        };

        return $this->callFunction($func, view('backoffice.my-activity.overtime.show'));
    }

    public function confirmOvertimeShow($id)
    {
        $func = function () use($id) {
            Gate::authorize('approvePolicy', EmployeeOvertime::class);

            $breadcrumbs = ['Aktifitas Saya', 'Persetujuan Lembur', 'Lihat Lembur'];
            $pageTitle = "Lihat Lembur";
            $overtime = $this->employeeOvertimeService->showOvertime($id);
            $this->data = compact('breadcrumbs', 'pageTitle','overtime');
        };

        return $this->callFunction($func, view('backoffice.my-activity.confirm-overtime.show'));
    }

    public function confirmOvertimeIndex()
    {
        $func = function () {
            Gate::authorize('approvePolicy', EmployeeOvertime::class);

            $breadcrumbs = ['Aktifitas Saya', 'Persetujuan Lembur'];
            $pageTitle = "Persetujuan Lembur";
            $flashMessageSuccess = session('flashMessageSuccess');
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess');
        };

        return $this->callFunction($func, view('backoffice.my-activity.confirm-overtime.index'));
    }

    public function confirmOvertimeUpdate($id, Request $request)
    {
        $func = function () use ($id, $request) {
            Gate::authorize('approvePolicy', EmployeeOvertime::class);
            $data = $request->validate([
                'overtime_pay' => 'integer'
            ]);
            $this->employeeOvertimeService->approveOvertime($id,$data);
            session()->flash('flashMessageSuccess', 'Task was successful!');

        };
        return $this->callFunction($func, null, 'my-activity.my-overtime-confirm.confirm-overtime-index');


    }

    public function cancelOvertimeUpdate($id)
    {
        $func = function () use ($id) {

            $this->employeeOvertimeService->cancelOvertime($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');

        };
        return $this->callFunction($func, null, 'my-activity.my-overtime.index');


    }

    public function rejectOvertimeUpdate($id)
    {
        $func = function () use ($id) {

            $this->employeeOvertimeService->rejectOvertime($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');

        };
        return $this->callFunction($func, null, 'my-activity.my-overtime-confirm.confirm-overtime-index');


    }

}
