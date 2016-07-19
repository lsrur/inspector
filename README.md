# Laravel Zoo Inspector
Laravel 5.x package to inspect, debug and profile many aspects of your server side code using your browser's console.

Laravel Inspector collects information of your objects, variables, database queries, request data, views, session, etc. and automatically sends it to yours browsers console, whatever your code returns views or ajax responses.

### Prerequisities
The package was developed using PHP 7 so there may be incompatibilities with PHP 5.

* PHP 7.x
* Laravel 5.x

### Installation
	composer require lsrur/inspector


### Configuration
After updating composer, add the ServiceProvider to the providers array in config/app.php:

	Lsrur\Inspector\InspectorServiceProvider::class,

  
And this Facade in the same configuration file:

	'Inspector' => Lsrur\Inspector\Facade\Inspector::class,
	
Finally pubhlish the configuration file:
	
	php artisan vendor:publish


## Sample Screenshot

![MacDown Screenshot](https://s31.postimg.org/vlfgyr21n/002.png)
  
## Usage
Laravel inspector will only be active if the enviroment variable APP_DEBUG is true.  
You can turn it off temporarily (just for the current request) with the following command:

	Inspector::turnOff();

By default, Inspector returns a Javascript to render the output on the browser console. If you want to force the output to JSON format for the current request, use the following:

	Inspector::toJson();
	
If you want to force the output to JSON format for all requests, edit the configuration variable `force_json_output` in the file `config/inspector.php`	

### Inspecting Objects and Variables
```php	
	//Using Facade
	Inspector::log(["description"], $myVar);
	Inspector::info(["description"], $myVar);
	Inspector::error(["description"], $myVar);
	Inspector::table(["description"], $myVar);
	
	//Using helper functions
	Inspector()->info(...);
	Inspect($var1, $var2, $var3, ...);
```	
	
### Grouping
```php	
	Inspector::group("myGroup");
		Inspector::info(["description"], $myVar);
		Inspector::error(["description"], $myVar);
		Inspector::table(["description"], $myVar);
		Inspector::group("mySubGroup");
			Inspector::info(["description"], $myVar);
		Inspector::endGroup();
	Inspector::endGroup();
```		
In addition to the ability to group information, each group excecution time will be measured and shown.

### Additional Information 
Each response will include information of:

* Database queries, including param bindings and excecution time
* Exceptions*
* Complete request information
* Data passed to views (in view responses)
* Previous inspections (in redirect responses)
* Session information
* Total allocated RAM and total script excecution time
* Configurable $_SERVER dump 

**In order to show exceptions you should add the following line under the "render" method of app/Exceptions/Handler.php**

```php    
    public function render($request, Exception $e)
    {
        \Inspector::addException($e); 	// <- THIS LINE
        return parent::render($request, $e);
    }  
```    
    
## License
Laravel Zoo Inspector is licensed under the MIT License.