<?php

namespace Brianpando\Plantumlgen;

use Illuminate\Support\ServiceProvider;

class PlantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        
        //include __DIR__.'/routes/web.php';
        //$this->app->make('Brianpando\Plantumlgen\PlantController');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(){
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\PlantMigrations::class,
                Commands\PlantModels::class,
            ]);
        }
    }
    // public function boot(\Illuminate\Routing\Router $router) {
    //     $this->commands([
    //         \Vendor\plantumlgen\Commands\PlantMigrations::class,
    //         \Vendor\plantumlgen\Commands\PlantModels::class,
    //     ]);
    // }
}
