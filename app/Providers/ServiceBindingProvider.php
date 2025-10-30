<?php

namespace App\Providers;

use App\Contracts\CartServiceInterface;
use App\Contracts\OrderServiceInterface;
use App\Contracts\PaymentServiceInterface;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Support\ServiceProvider;

class ServiceBindingProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        CartServiceInterface::class => CartService::class,
        OrderServiceInterface::class => OrderService::class,
        PaymentServiceInterface::class => PaymentService::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        // Bindings are automatically registered via $bindings property
        // But we can also manually bind if needed:
        
        // $this->app->bind(CartServiceInterface::class, CartService::class);
        // $this->app->bind(OrderServiceInterface::class, OrderService::class);
        // $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        
        // Or use singleton for services that should be shared:
        // $this->app->singleton(CartServiceInterface::class, CartService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
