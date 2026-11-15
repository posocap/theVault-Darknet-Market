<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Marketplace\Payment\{Payment, Escrow, Coin, StubCoin};


class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Return payment class
        $this -> app -> singleton(Payment::class, function($app, $parameters){
            return new Escrow($parameters['purchase']);
        });
        // Return coin class
        $this -> app -> singleton(Coin::class, function($app){
            // Simulation only; real coins will be re-enabled later
            return new StubCoin();
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
