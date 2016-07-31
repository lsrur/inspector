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
        
        if(\Inspector::isOn())
	    {
            if($request->get('laravel_inspector')=='dd')
            {
                \inspector::dd();
            } elseif($request->get('laravel_inspector')=='off') {
                // do nothing
            } elseif($request->get('laravel_inspector')=='dump') {
                
                \inspector::analize($request, $response);

            } else {
                \Inspector::injectInspection($request, $response);                
            }
        }
        return $response;
    }

}