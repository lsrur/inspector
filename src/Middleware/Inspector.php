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

        define('INSPECTOR_START', microtime(true));
        if(\Inspector::isOn())
	       	\Inspector::injectInspection($request, $response);

        return $response;
    }

}