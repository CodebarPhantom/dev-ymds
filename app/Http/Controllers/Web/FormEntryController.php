<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\MasterController;
use App\Models\Surah;
use Illuminate\Http\Request;
use App\Services\FormService;
use App\Services\SurahService;
use App\Services\UserService;
use App\Services\EntryHeaderService;
use App\Services\EntryDetailService;
use App\Services\LocationService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FormEntryController extends MasterController
{
    protected $formService, $surahService, $userService, $entryHeaderService, $entryDetailService, $locationService;


    // Inject multiple services through the constructor
    public function __construct(FormService $formService, SurahService $surahService, UserService $userService, EntryHeaderService $entryHeaderService, EntryDetailService $entryDetailService, LocationService $locationService)
    {
        parent::__construct();
        $this->formService = $formService;
        $this->surahService = $surahService;
        $this->userService = $userService;
        $this->entryHeaderService = $entryHeaderService;
        $this->entryDetailService = $entryDetailService;
        $this->locationService = $locationService;
    }

    public function create($formCode)
    {
        $func = function () use ($formCode) {
            //     // Authorization logic can be added here if needed
            $formData = $this->formService->getFormData($formCode);
            $breadcrumbs = ['Form Entry', 'Create', $formData['name']];
            $surahs = $this->surahService->getAllSurahForSelect();
            $pageTitle = $formData['name'];
            $users = $this->userService->getAllUserForSelect();
            $locations = $this->locationService->getAllLocationForSelect();
            // Determine if multiple locations exist. If more than 1 then true, otherwise false.
            $multiLocation = (is_countable($locations) ? count($locations) : 0) > 1;
            $this->data = compact('breadcrumbs', 'pageTitle', 'formCode', 'formData', 'surahs', 'users', 'locations', 'multiLocation');
        };

        return $this->callFunction($func, view('form.entry.create'));
    }

    public function store(Request $request)
    {
        $func = function () use ($request) {

            //Log::info('Form data received: ', $request->all());
            $entryCode = Carbon::now()->format('YmdHisv');
            // Validasi data
            $validated = $request->validate([
                'form_id' => 'required|integer',
                'user_id' => 'required|integer',
                'surah_id' => 'required|integer',
                'page' => 'required|integer',
                'verse_start' => 'required|integer',
                'verse_end' => 'required|integer',
                'entry_date' => 'required|date',
                'counts' => 'required|array',
                'counts.*' => 'integer',
                'notes' => 'nullable|string',
                'kelas' => 'nullable|string',
                'nilai' => 'nullable|string',
            ]);

            $metadata = [
                'kelas' => $request->kelas,
                'nilai' => $request->nilai,
            ];

            // Siapkan data untuk EntryHeader
            $headerData = [
                'form_id' => $request->form_id,
                'user_id' => $request->user_id,
                'surah_id' => $request->surah_id,
                'page' => $request->page,
                'verse_start' => $request->verse_start,
                'verse_end' => $request->verse_end,
                'entry_date' => $request->entry_date,
                'notes' => $request->notes ?? null,
                'approver_id' => Auth::id(), // Misalnya, user yang sedang login sebagai approver
                'entry_code' => $entryCode,
                'metadata' => $metadata,
            ];

            // Buat EntryHeader
            $entryHeader = $this->entryHeaderService->createEntryHeader($headerData);

            // Proses data counts untuk EntryDetail
            $counts = $request->counts;
            foreach ($counts as $questionId => $value) {
                $detailData = [
                    'entry_header_id' => $entryHeader->id,
                    'question_id' => $questionId,
                    'string_value' => (string) $value,
                    'entry_code' => $entryCode
                ];

                $this->entryDetailService->createEntryDetail($detailData);
            }

            $redirect = route('forms.create.tahsin-tilawah');
            $this->data = compact('redirect');
            $this->messages = ['Tahsin tilawah berhasil ditambahkan!'];
        };

        return $this->callFunction($func);
    }
}
