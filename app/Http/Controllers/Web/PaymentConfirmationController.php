<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\MasterController;
use App\Models\PaymentConfirmation;
use Illuminate\Http\Request;

class PaymentConfirmationController extends MasterController
{
    public function index()
    {
        $func = function () {


            $breadcrumbs = ['Konfirmasi Pembayaran'];
            $pageTitle = "Lokasi";
            $this->data = compact('breadcrumbs', 'pageTitle');
        };

        return $this->callFunction($func, view('admin.payment-confirmations.index'));
    }

    public function updateStatus(PaymentConfirmation $confirmation, Request $request)
    {
        $request->validate([
            'status' => 'required|in:butuh_pengecekan,sudah_dicek'
        ]);

        $confirmation->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diupdate',
            'status_label' => $confirmation->status_label
        ]);
    }

    public function export(Request $request)
    {
        $search = $request->input('search', '');
        $month = $request->input('month', '');
        $year = $request->input('year', '');

        $confirmations = PaymentConfirmation::with(['location', 'locationCategory'])
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->whereRaw('confirmation_code ILIKE ?', ['%'.$search.'%'])
                          ->orWhereRaw('payer_name ILIKE ?', ['%'.$search.'%'])
                          ->orWhereHas('location', function($subQ) use ($search) {
                              $subQ->whereRaw('name ILIKE ?', ['%'.$search.'%']);
                          })
                          ->orWhereHas('locationCategory', function($subQ) use ($search) {
                              $subQ->whereRaw('name ILIKE ?', ['%'.$search.'%']);
                          });
                }
            })
            ->when($month, function($query, $month) {
                return $query->where('month', $month);
            })
            ->when($year, function($query, $year) {
                return $query->where('year', $year);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $filename = 'konfirmasi-pembayaran-' . date('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($confirmations, $months) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($file, [
                'Kode Konfirmasi',
                'Nama Pembayar',
                'Kategori',
                'Lokasi',
                'Periode',
                'Nominal',
                'Status',
                'Tanggal Dibuat'
            ]);

            // Data
            foreach ($confirmations as $confirmation) {
                fputcsv($file, [
                    $confirmation->confirmation_code,
                    $confirmation->payer_name,
                    $confirmation->locationCategory->name,
                    $confirmation->location->name,
                    $months[$confirmation->month] . ' ' . $confirmation->year,
                    //'Rp ' . number_format($confirmation->amount, 0, ',', '.'),
                    $confirmation->amount,
                    $confirmation->status_label,
                    $confirmation->created_at->format('d/m/Y H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
