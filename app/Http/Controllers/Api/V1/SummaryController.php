<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use App\Models\EntryHeader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SummaryController extends MasterController
{

    // Inject multiple services through the constructor
    public function __construct()
    {
        parent::__construct();
    }

    public function dataTable(Request $request)
    {

        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10
        $sortField = $request->input('sortField', 'created_at'); // Default sort field
        $sortOrder = $request->input('sortOrder', 'desc'); // Default sort order


        $mapEntryHeaders = EntryHeader::with(['surah', 'approver', 'details'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize);
//Log::info('EntryHeaders fetched: ', $mapEntryHeaders);
        // Tambahkan atribut total_errors ke setiap item tanpa mengubah struktur asli
        $mapEntryHeaders->getCollection()->transform(function ($item) {
            $item->total_errors = $item->details->sum(function ($detail) {
                return (int) $detail->string_value;
            });
            return $item;
        });

        return response()->json([
            'page' => $mapEntryHeaders->currentPage(),
            'pageCount' => $mapEntryHeaders->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $mapEntryHeaders->total(),
            'data' => $mapEntryHeaders->items(),
        ]);
    }
}
