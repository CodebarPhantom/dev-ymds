<?php

namespace App\Http\Controllers\Web\MyActivity;

use App\Enums\PermitStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MasterController;
use App\Models\Workforce\EmployeePermit;
use App\Services\Workforce\EmployeePermitService;
use App\Enums\PermitType;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MyPermitController extends MasterController
{

    protected $employeePermitService;

    public function __construct(
        EmployeePermitService $employeePermitService
    ) {
        parent::__construct();
        $this->employeePermitService = $employeePermitService;
    }

    public function index()
    {
        $func = function () {

            $breadcrumbs = ['Aktifitas Saya', 'Izin'];
            $pageTitle = "Izin";
            $flashMessageSuccess = session('flashMessageSuccess');
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess');
        };

        return $this->callFunction($func, view('backoffice.my-activity.permit.index'));
    }

    public function create()
    {
        $func = function () {
            $breadcrumbs = ['Aktifitas Saya', 'Cuti', 'Buat Izin'];
            $pageTitle = "Buat Izin";
            $permitTypes = PermitType::cases();
            $this->data = compact('breadcrumbs', 'pageTitle', 'permitTypes');
        };

        return $this->callFunction($func, view('backoffice.my-activity.permit.create'));
    }

    public function store(Request $request)
    {
        $func = function () use ($request) {


            $data = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'note' => 'nullable|string',
                'permit_type' => 'required|integer',
                'url_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            ]);

            // Convert start_date and end_date to Carbon
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);

            // If permit type is "Sakit" (assuming 2 represents "Sakit")
            if ($data['permit_type'] == PermitType::SAKIT->value) {
                $data['start_date'] = $startDate->startOfDay(); // Set to 00:00:00
                $data['end_date'] = $endDate->endOfDay();       // Set to 23:59:59
            }

            if ($request->hasFile('url_image')) {
                $employeeName = auth()->user()->name; // Assuming employee name comes from the authenticated user
                $date = now()->format('Y-m-d'); // Current date format YYYY-MM-DD

                // Generate file path
                $fileName = 'permit_' . Str::random(10) . '.jpg';
                $filePath = 'permit/' . Str::slug($employeeName) . '/' . $date . '/' . $fileName;

                // Store the uploaded file
                Storage::disk('uploads')->put($filePath, file_get_contents($request->file('url_image')));

                // Save file path in data array
                $data['url_image'] ='uploads/'. $filePath;
            }


            $this->employeePermitService->createPermit($data);
            $this->messages = ['Pengajuan izin berhasil dibuat'];
            session()->flash('flashMessageSuccess');
        };

        return $this->callFunction($func, null, 'my-activity.my-permit.index');
    }

    public function show($id)
    {
        $func = function () use ($id) {
            $breadcrumbs = ['Aktifitas Saya', 'Izin', 'Lihat Izin'];
            $pageTitle = "Lihat Izin";
            $permit = $this->employeePermitService->showPermit($id);

            $this->data = compact('breadcrumbs', 'pageTitle', 'permit');
        };

        return $this->callFunction($func, view('backoffice.my-activity.permit.show'));
    }

    public function confirmPermitShow($id)
    {
        $func = function () use ($id) {
            Gate::authorize('approvePolicy', EmployeePermit::class);

            $breadcrumbs = ['Aktifitas Saya', 'Persetujuan Izin', 'Lihat Izin'];
            $pageTitle = "Lihat Izin";
            $permit = $this->employeePermitService->showPermit($id);
            $this->data = compact('breadcrumbs', 'pageTitle', 'permit');
        };

        return $this->callFunction($func, view('backoffice.my-activity.confirm-permit.show'));
    }

    public function confirmPermitIndex()
    {
        $func = function () {
            Gate::authorize('approvePolicy', EmployeePermit::class);

            $breadcrumbs = ['Aktifitas Saya', 'Persetujuan Izin'];
            $pageTitle = "Persetujuan Izin";
            $flashMessageSuccess = session('flashMessageSuccess');
            $this->data = compact('breadcrumbs', 'pageTitle', 'flashMessageSuccess');
        };

        return $this->callFunction($func, view('backoffice.my-activity.confirm-permit.index'));
    }

    public function confirmPermitUpdate($id)
    {
        $func = function () use ($id) {
            Gate::authorize('approvePolicy', EmployeePermit::class);

            $this->employeePermitService->approvePermit($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };
        return $this->callFunction($func, null, 'my-activity.my-permit-confirm.confirm-permit-index');
    }

    public function cancelPermitUpdate($id)
    {
        $func = function () use ($id) {

            $this->employeePermitService->cancelPermit($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };
        return $this->callFunction($func, null, 'my-activity.my-permit.index');
    }

    public function rejectPermitUpdate($id)
    {
        $func = function () use ($id) {

            $this->employeePermitService->rejectPermit($id);
            session()->flash('flashMessageSuccess', 'Task was successful!');
        };
        return $this->callFunction($func, null, 'my-activity.my-permit-confirm.confirm-permit-index');
    }
}
