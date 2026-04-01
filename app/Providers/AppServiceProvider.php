<?php

namespace App\Providers;

use App\Models\Location;
use App\Policies\LocationPolicy;
use App\Models\Permission;
use App\Policies\PermissionPolicy;
use App\Models\PermissionGroup;
use App\Policies\PermissionGroupPolicy;
use App\Models\Role;
use App\Policies\RolePolicy;
use App\Models\User;
use App\Policies\UserPolicy;


use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');

        if($this->app->environment('production')) {
            \URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
            }
        });

        //Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        Gate::policy(User::class, UserPolicy::class);
        //Gate::policy(EmployeeLeave::class, MyLeavePolicy::class);
        Gate::policy(Location::class, LocationPolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(PermissionGroup::class, PermissionGroupPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

    }
}
