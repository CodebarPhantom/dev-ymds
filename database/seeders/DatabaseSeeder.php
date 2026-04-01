<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Database\Seeders\Permission\PermissionSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Check if the user exists

        $location = Location::firstOrCreate(['name' => 'B1/10'], ['name' => 'B1/10', 'phone' => 'XXXXXXX', 'address' => 'XXXXXXX']);

        $role = Role::firstOrCreate(
            ['name' => 'Administrator', 'guard_name' => 'web'],
            ['name' => 'Administrator', 'guard_name' => 'web']
        );
        $user = User::where('email', 'eryanfauzan10@gmail.com')->first();

        if (!$user) {
            // If user doesn't exist, create a new user
            $user = User::create([
                'name' => 'Eryan Fauzan',
                'email' => 'eryanfauzan10@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('admin@20245'),
                'remember_token' => Str::random(10),
                'location_id' => $location->id
            ]);
        }

        // Create or find the 'administrator' role


        // Run the PermissionSeeder
        $this->call([
            PermissionSeeder::class
        ]);

        // Fetch all permissions created by PermissionSeeder
        $permissions = Permission::pluck('name')->toArray();

        // Assign all the permissions to the 'administrator' role
        $role->syncPermissions($permissions); // syncPermissions to assign the fetched permissions

        // Assign or sync the role to the user
        if ($user) {
            $user->syncRoles([$role]); // Sync roles to avoid duplicates
        }
    }
}
