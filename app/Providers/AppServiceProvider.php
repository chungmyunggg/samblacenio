<?php

namespace App\Providers;

use App\Models\Flight;
use App\Models\Post;
use App\Models\User;
use App\Models\Scopes\SortByCreatedAtScope;
use App\Observers\UserObserver;
use App\Policies\PostPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        // Register Observers
        User::observe(UserObserver::class);

        // Global Scopes
        // Registering the SortByCreatedAtScope globally for Flights ensures they always appear newest first
        Flight::addGlobalScope(new SortByCreatedAtScope);

        // Local Scopes (implemented as Macros since we are modifying the Provider)
        // Usage: Flight::expensive()->get();
        Builder::macro('expensive', function () {
            /** @var Builder $this */
            return $this->where('price', '>', 1000);
        });

        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        Gate::policy(Post::class, PostPolicy::class);

        // Configure Eloquent Strictness
        // Prevents N+1 queries and mass assignment errors in non-production environments
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());
    }
}
