<?php

namespace Lsrur\Inspector;

use Illuminate\Support\ServiceProvider;

class InspectorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    
        if(\DB::connection()->getDatabaseName())
        { 
           \DB::listen(function($sql) {
                \Inspector::addSql($sql);
            });
        }

        if (is_dir(base_path() . '/resources/views/packages/lsrur/inspector')) {
            $this->loadViewsFrom(base_path() . '/resources/views/packages/lsrur/inspector', 'inspector');
        } else {
            // The package views have not been published. Use the defaults.
            $this->loadViewsFrom(__DIR__.'/views', 'inspector');
        }

        //Register Middleware
        $kernel = $this->app->make('Illuminate\Contracts\Http\Kernel');
        $kernel->pushMiddleware('Lsrur\Inspector\Middleware\Inspector');
        $this->publishes([
            __DIR__.'/config/inspector.php' => base_path('config')
            ]);
        
        $this->mergeConfigFrom(__DIR__.'/config/inspector.php', 'inspector'); 

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Inspector', function(){
            return new Inspector;
        });
    }
}
