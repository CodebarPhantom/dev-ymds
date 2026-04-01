<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\MasterController;
use App\Models\LocationCategory;
use App\Models\Location;
use App\Models\PaymentConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class LandingController extends MasterController
{
    public function index()
    {
        $categories = LocationCategory::orderBy('sort_order','ASC')->get();
        return view('welcome', compact('categories'));
    }

    public function filter($categoryId)
    {
        // Get category by id
        $category = LocationCategory::findOrFail($categoryId);

        // Get locations filtered by category
        $locations = Location::where('location_category_id', $categoryId)->active()->get();

        // Get block categories for chained selection (if current category is not block)
        $blockCategories = LocationCategory::where('type', 'block')->orderBy('sort_order')->get();

        // Return view dengan data yang sudah difilter
        return view('filtered', [
            'category' => $category,
            'locations' => $locations,
            'blockCategories' => $blockCategories,
        ]);
    }

    public function getLocationsByBlock($blockId)
    {
        $locations = Location::where('location_category_id', $blockId)->orderBy('name','ASC')->active()->get();
        return response()->json($locations);
    }

    public function submitPayment(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'location_category_id' => 'required|exists:location_categories,id',
            'payer_name' => 'required|string|max:255',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2025',
            'amount' => 'required|numeric|min:0',
            'proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes' => 'nullable|string'
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            $proofPath = $request->file('proof_file')->store('payment-proofs', 'public');
        }

        $confirmation = PaymentConfirmation::create([
            'confirmation_code' => 'PAY-' . strtoupper(Str::random(8)),
            'location_id' => $request->location_id,
            'location_category_id' => $request->location_category_id,
            'payer_name' => $request->payer_name,
            'month' => $request->month,
            'year' => $request->year,
            'amount' => $request->amount,
            'proof_file' => $proofPath,
            'notes' => $request->notes,
        ]);

        $confirmation->load(['location', 'locationCategory']);

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return response()->json([
            'success' => true,
            'message' => 'Konfirmasi pembayaran berhasil dikirim!',
            'data' => $confirmation,
            'redirect_url' => route('payment.summary', $confirmation->id)
        ]);
    }

    public function paymentSummary(PaymentConfirmation $confirmation)
    {
        $confirmation->load(['location', 'locationCategory']);

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $whatsappMessage =
        "Halo, saya {$confirmation->payer_name}, blok {$confirmation->location->name}. " .
        "Saya ingin konfirmasi pembayaran dengan kode {$confirmation->confirmation_code} untuk kategori {$confirmation->locationCategory->name}, " .
        "sebesar Rp " . number_format($confirmation->amount, 0, ',', '.') . " " .
        "untuk periode {$months[$confirmation->month]} {$confirmation->year}" .
        ($confirmation->notes ? " (catatan: {$confirmation->notes})" : "") .
        ". Terima kasih.";

        return view('payment-summary', compact('confirmation', 'whatsappMessage'));
    }
}
