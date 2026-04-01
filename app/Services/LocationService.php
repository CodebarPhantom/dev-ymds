<?php

namespace App\Services;

use App\Models\Location;
use App\Services\MasterService;

class LocationService extends MasterService
{
    public function storeLocation(array $data)
    {
        $location = Location::create($data);
        return $location;
    }

    public function showLocation(int $id)
    {
        return Location::where('id', $id)->first();
    }

    public function updateLocation($id, array $data)
    {
        $location = Location::find($id);
        $location->update($data);
        return $location;
    }

    public function deleteLocation($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        return $location;
    }
    public function getAllLocations()

    {
        return Location::active()->get();
    }

    public function getAllLocationForSelect()
    {
        $authLocations = json_decode(auth()->user()->location_id, true) ?? [];

        return Location::active()
            ->whereIn('id', $authLocations)
            ->orderBy('name', 'asc')
            ->get()
            ->map(fn($location) => [
                'id' => $location->id,
                'label' => $location->name,
            ]);
    }
}
