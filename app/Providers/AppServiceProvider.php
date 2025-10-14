<?php

namespace App\Providers;

use App\View\Composers\NavigationComposer;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
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
        // Remove data wrapping for API resources
        JsonResource::withoutWrapping();

        // Use Bootstrap for pagination
        Paginator::useBootstrapFive();

        // Share categories and cart count với tất cả views customer
        View::composer([
            'layouts.customer',
            'home',
            'products.*',
            'cart.*',
        ], NavigationComposer::class);
    }
}
