<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class PlantumlgenServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(\Illuminate\Routing\Router $router) {
        $this->commands([
            \Vendor\plantumlgen\Commands\PlantMigrations::class,
            \Vendor\plantumlgen\Commands\PlantModels::class,
        ]);
    }
}
