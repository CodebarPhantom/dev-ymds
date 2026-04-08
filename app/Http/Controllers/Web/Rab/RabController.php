<?php

namespace App\Http\Controllers\Web\Rab;

use App\Http\Controllers\MasterController;
use App\Models\Rab;
use App\Services\RabService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RabController extends MasterController
{
    protected RabService $rabService;

    public function __construct(RabService $rabService)
    {
        parent::__construct();
        $this->rabService = $rabService;
    }

    public function index()
    {
        $func = function () {
            Gate::authorize('readPolicy', Rab::class);

            $breadcrumbs = ['RAB'];
            $pageTitle = 'Daftar RAB';
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('rab.index'));
    }

    public function create()
    {
        $func = function () {
            Gate::authorize('createPolicy', Rab::class);

            $breadcrumbs = ['RAB', 'Buat RAB'];

            $pageTitle = 'Buat RAB';
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('rab.create'));
    }

    public function store(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('createPolicy', Rab::class);

            $request->validate([
                'bulan_pengajuan'            => 'required|date',
                'items'                      => 'required|array|min:1',
                'items.*.kegiatan'           => 'required|string|max:255',
                'items.*.catatan_kegiatan'   => 'nullable|string',
                'items.*.biaya_anggaran'     => 'required|numeric|min:0',
                'items.*.waktu_pelaksanaan'  => 'required|string|max:100',
            ], [
                'items.*.biaya_anggaran.required' => 'Biaya anggaran wajib diisi.',
                'items.*.biaya_anggaran.numeric'  => 'Biaya anggaran harus berupa angka.',
                'items.*.biaya_anggaran.min'      => 'Biaya anggaran tidak boleh bernilai negatif.',
            ]);

            $this->rabService->storeRab($request->all(), auth()->user());
            $this->messages = ['RAB berhasil dibuat.'];
        };

        return $this->callFunction($func, null, 'rab.index');
    }

    public function show(Rab $rab)
    {
        $func = function () use ($rab) {
            Gate::authorize('readPolicy', Rab::class);

            $user = auth()->user();
            $roleName = $user->getRoleNames()->first();

            if ($roleName && str_starts_with($roleName, 'divisi_') && $rab->divisi_id !== $roleName) {
                abort(403);
            }

            $rab->load(['items', 'approvalLogs.dilakukanOleh']);

            $breadcrumbs = ['RAB', 'Lihat RAB'];

            $pageTitle = 'Detail RAB';
            $this->data = compact('breadcrumbs', 'pageTitle', 'rab');
        };

        return $this->callFunction($func, view('rab.show'));
    }

    public function edit(Rab $rab)
    {
        $func = function () use ($rab) {
            Gate::authorize('updatePolicy', $rab);

            $rab->load('items');

            $breadcrumbs = ['RAB', 'Edit RAB'];
            $pageTitle = 'Edit RAB';
            $this->data = compact('breadcrumbs', 'pageTitle', 'rab');
        };

        return $this->callFunction($func, view('rab.edit'));
    }

    public function update(Request $request, Rab $rab)
    {
        $func = function () use ($request, $rab) {
            Gate::authorize('updatePolicy', $rab);

            $request->validate([
                'bulan_pengajuan'            => 'required|date',
                'items'                      => 'required|array|min:1',
                'items.*.kegiatan'           => 'required|string|max:255',
                'items.*.catatan_kegiatan'   => 'nullable|string',
                'items.*.biaya_anggaran'     => 'required|numeric|min:0',
                'items.*.waktu_pelaksanaan'  => 'required|string|max:100',
            ], [
                'items.*.biaya_anggaran.required' => 'Biaya anggaran wajib diisi.',
                'items.*.biaya_anggaran.numeric'  => 'Biaya anggaran harus berupa angka.',
                'items.*.biaya_anggaran.min'      => 'Biaya anggaran tidak boleh bernilai negatif.',
            ]);

            $this->rabService->updateRab($rab, $request->all());
            $this->messages = ['RAB berhasil diperbarui.'];
        };

        return $this->callFunction($func, null, 'rab.index');
    }
}
