<?php 
if (!function_exists('inspector')) {
    
    function inspector($value = null)
    {
        return app('Inspector');
    }
}; 

if (!function_exists('inspect')) {
    
    function inspect($value)
    {
        $inspector = app('Inspector');
        foreach (func_get_args() as $value) {
            $inspector->info($value);
        }
    }
};

