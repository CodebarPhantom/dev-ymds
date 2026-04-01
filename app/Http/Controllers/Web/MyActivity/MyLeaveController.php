<?php

namespace App\Http\Controllers\Web\MyActivity;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MasterController;
use App\Models\Workforce\EmployeeLeave;
use App\Services\Workforce\EmployeeLeaveService;
use Illuminate\Support\Facades\Gate;

class MyLeaveController extends MasterController
{

    protected $employeeLeaveService;

    public function __construct(
        EmployeeLeaveService $employeeLeaveService
    ) {
        parent::__construct();
        $this->employeeLeaveService = $employeeLeaveService;
    }

    public function index()
    {
        $func = function () {

            $breadcrumbs = ['Aktifitas Saya', 'Cuti'];
            $pageTitle = "Cuti";
            $flashMessageSuccess = session('flashMessageSuccess');
            $remainingLeave = $this->employeeLeaveService->remainingLeave(Auth::user()->employee_id);
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess', 'remainingLeave');
        };

        return $this->callFunction($func, view('backoffice.my-activity.leave.index'));
    }

    public function create()
    {
        $func = function () {
            $breadcrumbs = ['Aktifitas Saya', 'Cuti', 'Buat Cuti'];
            $pageTitle = "Buat Cuti";
            $remainingLeave = $this->employeeLeaveService->remainingLeave(Auth::user()->employee_id);
            $this->data = compact('breadcrumbs', 'pageTitle', 'remainingLeave');
        };

        return $this->callFunction($func, view('backoffice.my-activity.leave.create'));
    }

    public function store(Request $request)
    {
        $func = function () use ($request) {


            $data = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'note' => 'nullable|string'
            ]);

            $this->employeeLeaveService->createLeave($data);
            $this->messages = ['Pengajuan cuti berhasil dibuat'];
            session()->flash('flashMessageSuccess');
        };

        return $this->callFunction($func, null, 'my-activity.my-leave.index');
    }

    public function show($id)
    {
        $func = function () use($id) {
            $breadcrumbs = ['Aktifitas Saya', 'Cuti', 'Lihat Cuti'];
            $pageTitle = "Lihat Cuti";
            $remainingLeave = $this->employeeLeaveService->remainingLeave(Auth::user()->employee_id);
            $leave = $this->employeeLeaveService->showLeave($id);

            $this->data = compact('breadcrumbs', 'pageTitle', 'remainingLeave','leave');
        };

        return $this->callFunction($func, view('backoffice.my-activity.leave.show'));
    }

    public function confirmLeaveShow($id)
    {
        $func = function () use($id) {
            Gate::authorize('approvePolicy', EmployeeLeave::class);

            $breadcrumbs = ['Aktifitas Saya', 'Persetujuan Cuti', 'Lihat Cuti'];
            $pageTitle = "Lihat Cuti";
            $leave = $this->employeeLeaveService->showLeave($id);
            $this->data = compact('breadcrumbs', 'pageTitle','leave');
        };

        return $this->callFunction($func, view('backoffice.my-activity.confirm-leave.show'));
    }

    public function confirmLeaveIndex()
    {
        $func = function () {
            Gate::authorize('approvePolicy', EmployeeLeave::class);

            $breadcrumbs = ['Aktifitas Saya', 'Persetujuan Cuti'];
            $pageTitle = "Persetujuan Cuti";
            $flashMessageSuccess = session('flashMessageSuccess');
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess');
        };

        return $this->callFunction($func, view('backoffice.my-activity.confirm-leave.index'));
    }

    public function confirmLeaveUpdate($id)
    {
        $func = function () use ($id) {
            Gate::authorize('approvePolicy', EmployeeLeave::class);

            $this->employeeLeaveService->approveLeave($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');

        };
        return $this->callFunction($func, null, 'my-activity.my-leave-confirm.confirm-leave-index');


    }

    public function cancelLeaveUpdate($id)
    {
        $func = function () use ($id) {

            $this->employeeLeaveService->cancelLeave($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');

        };
        return $this->callFunction($func, null, 'my-activity.my-leave.index');


    }

    public function rejectLeaveUpdate($id)
    {
        $func = function () use ($id) {

            $this->employeeLeaveService->rejectLeave($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');

        };
        return $this->callFunction($func, null, 'my-activity.my-leave-confirm.confirm-leave-index');


    }

}
