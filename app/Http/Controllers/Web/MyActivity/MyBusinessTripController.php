<?php

namespace App\Http\Controllers\Web\MyActivity;

use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use App\Enums\PermitType;
use App\Models\Workforce\EmployeeBusinessTrip;
use App\Services\Workforce\EmployeeBusinessTripService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MyBusinessTripController extends MasterController
{

    protected $employeeBusinessTripService;

    public function __construct(
        EmployeeBusinessTripService $employeeBusinessTripService
    ) {
        parent::__construct();
        $this->employeeBusinessTripService = $employeeBusinessTripService;
    }

    public function index()
    {
        $func = function () {

            $breadcrumbs = ['Aktifitas Saya', 'Perjalanan Dinas'];
            $pageTitle = "Perjalanan Dinas";
            $flashMessageSuccess = session('flashMessageSuccess');
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess');
        };

        return $this->callFunction($func, view('backoffice.my-activity.business-trip.index'));
    }

    public function create()
    {
        $func = function () {
            $breadcrumbs = ['Aktifitas Saya', 'Cuti', 'Buat Perjalanan Dinas'];
            $pageTitle = "Buat Perjalanan Dinas";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('backoffice.my-activity.business-trip.create'));
    }

    public function store(Request $request)
    {
        $func = function () use ($request) {


            $data = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'note' => 'nullable|string',
            ]);

            $data['start_date'] = Carbon::parse($data['start_date'])->startOfDay(); // Set to 00:00:00
            $data['end_date'] = Carbon::parse($data['end_date'])->endOfDay();       // Set to 23:59:59


            $this->employeeBusinessTripService->createBusinessTrip($data);
            $this->messages = ['Pengajuan perjalan dinas berhasil dibuat'];
            session()->flash('flashMessageSuccess');
        };

        return $this->callFunction($func, null, 'my-activity.my-business-trip.index');
    }

    public function show($id)
    {
        $func = function () use ($id) {
            $breadcrumbs = ['Aktifitas Saya', 'Perjalanan Dinas', 'Lihat Perjalanan Dinas'];
            $pageTitle = "Lihat Perjalanan Dinas";
            $businessTrip = $this->employeeBusinessTripService->showBusinessTrip($id);

            $this->data = compact('breadcrumbs', 'pageTitle', 'businessTrip');
        };

        return $this->callFunction($func, view('backoffice.my-activity.business-trip.show'));
    }

    public function confirmBusinessTripShow($id)
    {
        $func = function () use ($id) {
            Gate::authorize('approvePolicy', EmployeeBusinessTrip::class);

            $breadcrumbs = ['Aktifitas Saya', 'Persetujuan Izin', 'Lihat Izin'];
            $pageTitle = "Lihat Izin";
            $businessTrip = $this->employeeBusinessTripService->showBusinessTrip($id);
            $this->data = compact('breadcrumbs', 'pageTitle', 'businessTrip');
        };

        return $this->callFunction($func, view('backoffice.my-activity.confirm-business-trip.show'));
    }

    public function confirmBusinessTripIndex()
    {
        $func = function () {
            Gate::authorize('approvePolicy', EmployeeBusinessTrip::class);

            $breadcrumbs = ['Aktifitas Saya', 'Persetujuan Perjalanan Dinas'];
            $pageTitle = "Persetujuan Perjalanan Dinas";
            $flashMessageSuccess = session('flashMessageSuccess');
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess');
        };

        return $this->callFunction($func, view('backoffice.my-activity.confirm-business-trip.index'));
    }

    public function confirmBusinessTripUpdate($id)
    {
        $func = function () use ($id) {
            Gate::authorize('approvePolicy', EmployeeBusinessTrip::class);

            $this->employeeBusinessTripService->approveBusinessTrip($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };
        return $this->callFunction($func, null, 'my-activity.my-business-trip-confirm.confirm-business-trip-index');
    }

    public function cancelBusinessTripUpdate($id)
    {
        $func = function () use ($id) {

            $this->employeeBusinessTripService->cancelBusinessTrip($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };
        return $this->callFunction($func, null, 'my-activity.my-business-trip.index');
    }

    public function rejectBusinessTripUpdate($id)
    {
        $func = function () use ($id) {

            $this->employeeBusinessTripService->rejectBusinessTrip($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };
        return $this->callFunction($func, null, 'my-activity.my-business-trip-confirm.confirm-business-trip-index');
    }
}
