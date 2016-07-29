<?php 
if (!function_exists('inspector')) {
    
    function inspector($value = null)
    {
        return app('Inspector');
    }
}; 

if (!function_exists('li')) {
    
    function li($value = null)
    {
        if(! app('Inspector')->isOn() ) return;
        return app('Inspector');
    }
}; 

if (!function_exists('inspect')) {
    
    function inspect($value)
    {
        $inspector = app('Inspector');
        if(! $inspector->isOn()) return;
        foreach (func_get_args() as $value) {
            $inspector->info($value);
        }
    }
};

if (!function_exists('idd')) {
    
    function idd($value=null)
    {
        $inspector = app('Inspector');
        if(! $inspector->isOn()) return;
        foreach (func_get_args() as $value) {
            $inspector->info($value);
        }
        $inspector->dd();
    }
};

