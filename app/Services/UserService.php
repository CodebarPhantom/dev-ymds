<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Workforce\Employee;
use App\Services\MasterService;
use Illuminate\Support\Facades\Log;

class UserService extends MasterService
{
    public function createUser(array $data)
    {
        $role = Role::find($data['role_id']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'location_id' => $data['location_id'],
            'role_id' => $data['role_id']
        ]);
        $user->syncRoles([$role]);

        return;
    }

    // Service: UserService.php

    /**
     * Update user dengan ID tertentu
     *
     * @param  int    $id
     * @param  array  $data
     * @return \App\Models\User
     */
    public function updateUser(int $id, array $data)
    {
        $user = User::findOrFail($id);
        $role = Role::find($data['role_id']);


        // Kalau ada password baru, hash dulu
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Siapkan payload update dasar
        $updatePayload = [
            'name'        => $data['name'],
            'email'       => $data['email'],
            'location_id' => $data['location_id'],
            'is_active'   => $data['is_active'],
            'role_id'     => $data['role_id']
        ];

        // Jika ada password (hash sudah diset), tambahkan
        if (isset($data['password'])) {
            $updatePayload['password'] = $data['password'];
        }

        // Lakukan update
        $user->update($updatePayload);

        // Sync ulang role
        $user->syncRoles([$role]);

        return $user;
    }

    public function showUser($id)
    {
        return User::with(['location', 'role'])->where('id', $id)->first();
    }

    public function showUserByEmployeeId($id)
    {
        return User::where('employee_id', $id)->first();
    }

    public function createOrUpdateUserByEmployeeId($id, array $data)
    {

        $user = User::where('employee_id', $id)->first();
        $employee = Employee::find($id);
        $role = Role::find($employee->role_id);

        // Check if the password field exists and is not null in the $data array
        $password = isset($data['password']) && !empty($data['password']) ? bcrypt($data['password']) : null;

        $imagePath = null;
        if (isset($data['url_image']) && $data['url_image'] instanceof \Illuminate\Http\UploadedFile) {
            // Save the image to a desired directory, for example, 'uploads/profile_pictures'
            $imagePath = $data['url_image']->store('profile_pictures', 'uploads');
        }

        if ($user) {
            // Update the existing user
            $user->name = $data['name'];
            $user->email = $data['email'];

            // Only update password if a new one is provided
            if ($password) {
                $user->password = $password;
            }

            // Update the profile image URL if a new image is uploaded
            if ($imagePath) {
                $user->url_image = 'uploads/' . $imagePath;
            }

            $user->save();
        } else {
            // Create a new user
            $user = User::create([
                'employee_id' => $id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $password ?: bcrypt(Str::random(8)), // If no password is provided, set a random one
                'url_image' => $imagePath, // Store the uploaded image path if present
            ]);
        }

        $user->syncRoles([$role]);
    }

    public function getAllUsers()
    {
        return User::get();
    }

    public function getAllUserForSelect($locationId = null)
    {
        $authLocations = json_decode(auth()->user()->location_id, true) ?? [];

        $query = User::active();
        // If a specific locationId is provided, filter by that single location
        if (!is_null($locationId)) {
            // If a single location id is provided, normalize numeric strings to int
            // so it matches JSON arrays stored as numbers (e.g. [1]).
            $locFilter = is_array($locationId) ? $locationId : (is_numeric($locationId) ? (int) $locationId : $locationId);
            $query->whereJsonContains('location_id', $locFilter);
        } else {
            // Otherwise, apply the auth user's allowed locations (OR conditions)
            $query->where(function ($q) use ($authLocations) {
                foreach ($authLocations as $loc) {
                    $q->orWhereJsonContains('location_id', $loc);
                }
            });
        }

        return $query
            ->orderBy('name', 'asc')
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'label' => $user->name,
            ]);
    }
}
