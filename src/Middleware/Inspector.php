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
        if(env('APP_DEBUG'))
	       	\Inspector::injectScript($request, $response);

        return $response;
    }

}