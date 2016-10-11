<?php
namespace Lsrur\Inspector\Middleware;


use Closure;
use Illuminate\Support\Facades\Auth;

class Inspector
{
    public function handle($request, Closure $next)
    {
        try {
            $response = $next($request);
        } catch (Exception $e) {
            $response = $this->handleException($request, $e);
        }

        if(\Lsrur\Inspector\Facade\Inspector::isOn())
        {
            if($request->get('laravel_inspector')=='dd')
            {
                \Lsrur\Inspector\Facade\Inspector::dd();
            } elseif($request->get('laravel_inspector')=='off') {
                // do nothing
            } elseif($request->get('laravel_inspector')=='dump') {

                \Lsrur\Inspector\Facade\Inspector::analize($request, $response);

            } else {
                \Lsrur\Inspector\Facade\Inspector::injectInspection($request, $response);
            }
        }
        return $response;
    }

}
