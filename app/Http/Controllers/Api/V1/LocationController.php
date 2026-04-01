<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\MasterController;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Services\LocationService;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\PaymentConfirmation;
use Illuminate\Support\Facades\Log;

class LocationController extends MasterController
{
    protected $locationService;

    // Inject multiple services through the constructor
    public function __construct(LocationService $locationService)
    {
        parent::__construct();
        $this->locationService = $locationService;
    }

    public function dataTable( Request $request){
        Gate::authorize('readPolicy', Location::class); // is from policy

        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10
        $sortField = $request->input('sortField', 'name'); // Default sort field
        $sortOrder = $request->input('sortOrder', 'asc'); // Default sort order

        // Validate sort field and order
        $allowedSortFields = ['name']; // Add your sortable fields
        $allowedSortOrders = ['asc', 'desc'];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'name'; // Fallback to default
        }

        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'asc'; // Fallback to default
        }

        $locations = Location::where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->whereRaw('name ILIKE ?', ['%'.$search.'%']);
            }
        })
        ->orderBy($sortField, $sortOrder)
        ->paginate($pageSize);

        $locationData = $locations->map(function($location) {
            return [
                'id' => $location->id,
                'name' => $location->name,
                'address' => $location->address,
                'phone' => $location->phone,
                'status'=> $location->status,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'statusName'=> $location->status_name,
                'statusColor' => $location->status_color, // Accessor used here
            ];
        });


                       // Prepare the response
        return response()->json([
            'page' => $locations->currentPage(),
            'pageCount' => $locations->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $locations->total(),
            'data' =>  $locationData,
        ]);
    }

    public function destroy($id)
    {
        $func = function () use ($id) {

            Gate::authorize('deletePolicy', Location::class); // is from policy

            $this->locationService->deleteLocation($id);

        };

        return $this->callFunction($func, null, null);
    }

    public function getCombobox()
    {
        $func = function () {
            //Gate::authorize('readPolicy', Employee::class);

            $locationData = $this->locationService->getAllLocations()->select('id', 'name')->toArray();

            $locations = array_map(function ($item) {
                return [
                    "id" => $item["id"],
                    "label" => $item["name"], // Change 'name' to 'label'
                ];
            }, $locationData);

            $this->data = compact('locations');
        };

        return $this->callFunction($func);
    }

    public function getLocationsByBlock($blockId)
    {
        $func = function () use ($blockId) {
            $locations = Location::where('location_category_id', $blockId)
                ->orderBy('name','ASC')
                ->active()
                ->get();
            
            $this->data = $locations;
        };

        return $this->callFunction($func);
    }

    public function trackPayment($confirmationCode)
    {
        $func = function () use ($confirmationCode) {
            $confirmation = PaymentConfirmation::with(['location', 'locationCategory'])
                ->where('confirmation_code', $confirmationCode)
                ->first();

            if (!$confirmation) {
                $this->success = false;
                $this->message = 'Kode konfirmasi tidak ditemukan';
                return;
            }

            $months = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
            ];

            $this->data = [
                'id' => $confirmation->id,
                'confirmation_code' => $confirmation->confirmation_code,
                'payer_name' => $confirmation->payer_name,
                'category' => $confirmation->locationCategory->name,
                'location' => $confirmation->location->name,
                'month' => $confirmation->month,
                'year' => $confirmation->year,
                'amount' => $confirmation->amount,
                'amount_formatted' => number_format($confirmation->amount, 0, ',', '.'),
                'status' => $confirmation->status,
                'status_label' => $confirmation->status_label,
                'created_at' => $confirmation->created_at->format('d/m/Y H:i'),
            ];
        };

        return $this->callFunction($func);
    }
}
