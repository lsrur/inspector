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



        app()->booted(function(){

            if(!defined('LARAVEL_BOOTED')) {
                define('LARAVEL_BOOTED', microtime(true));
            }
        });

        // \View::composer('*', function($view)
        // {
        //     // prifile views?
        // });

        \Blade::directive('li', function($args) {

            $args = explode(',',str_replace(["(", ")"],'', $args));
            $cmd = str_replace(["'", '"'], '', $args[0]);
            array_shift($args);
            $args = implode(',',$args);
            return "<?php li()->$cmd($args); ?>";
        });


        if(\DB::connection()->getDatabaseName())
        {
           \DB::listen(function($sql) {
                \Lsrur\Inspector\Facade\Inspector::addSql($sql);
            });
        }

        if (is_dir(base_path() . '/resources/views/packages/lsrur/inspector')) {
            $this->loadViewsFrom(base_path() . '/resources/views/packages/lsrur/inspector', 'inspector');
        } else {
            // The package views have not been published. Use the defaults.
            $this->loadViewsFrom(__DIR__.'/views', 'inspector');
        }

        $kernel = $this->app->make('Illuminate\Contracts\Http\Kernel');
        $kernel->pushMiddleware('Lsrur\Inspector\Middleware\Inspector');

         $this->publishes([
            __DIR__.'/config/inspector.php' => config_path('inspector.php')], 'config');


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
