**UNDER HEAVY DEVELOPMENT**

# Laravel Inspector
Laravel 5.x package to inspect, debug and profile many aspects of your server side code using your browser's console.

Laravel Inspector collects information of objects, variables, database queries, request data, views, exceptions, session, etc. and automatically sends it to the browsers console.

* [Installation](#installation)
* [At a Glance](#glance)
* [Messages](#messages)
* [Groups](#data)
* [Conditionals](#status)
* [Dump & Die](#errors)
* [Exception handling](#errors)


### Prerequisities
The package was developed using PHP 7 so there may be incompatibilities with PHP 5.

* PHP 7.x
* Laravel 5.x

### Installation
	composer require lsrur/inspector


After updating composer, add the ServiceProvider to the providers array in config/app.php:

	Lsrur\Inspector\InspectorServiceProvider::class,

  
And this Facade in the same configuration file:

	'Inspector' => Lsrur\Inspector\Facade\Inspector::class,
	
Pubhlish the configuration file:
	
	php artisan vendor:publish

### Configuration

In order to include exceptions in Inspector responses and/or use Inspector as your exceptions renderer, add the following line in the file <code>app/Exceptions/Handler.php</code> of your Laravel project:

```php    
    public function render($request, Exception $e)
    {
        \Inspector::addException($e); 	// <- THIS LINE
        return parent::render($request, $e);
    }  
```      

Configuration variables <code>app/config/inspector.php</code>

```php    
	return [
	
	// change to "json" if you want to force Inspector to send jsons instead of scritps 
	'ajax_output' => 'script',

	// Use Laravel Inspector as exception renderer during development time (app.debug=true)
	'exception_render' => true,

	// Hide those keys form $_SERVER dump
    'hide_server_keys' => ['APP_KEY', 'DB_HOST', 'DB_PASSWORD', 'DB_USERNAME', 'HTTP_COOKIE', 	'MAIL_PASSWORD', 'REDIS_PASSWORD'],

];

```

## Sample Screenshot

|||
|:-:|:-:|
| sadasdadasdasdasd | ![pepe](https://s31.postimg.org/fs5jjuh9j/006.png) |
| sadasdadasdasdasd | ![pepe](https://s31.postimg.org/fs5jjuh9j/006.png) |
| sadasdadasdasdasd | ![pepe](https://s31.postimg.org/fs5jjuh9j/006.png) |

## Usage
Laravel inspector will be active if only the config variable app.debug is true.  
You can turn Inspector off temporarily (just for the current request) with the following command:

	\Inspector::turnOff();

By default, Inspector returns a Javascript to render the output on the browser console. If you want to force the output to JSON format for the current request, use the following:

	\Inspector::toJson();
	
If you want to force the output to JSON format for all requests, edit the configuration variable 
`force_json_output` in the file `config/inspector.php`	

### Inspecting objects and variables
The available methods are <code>info</code>,<code>log</code>,<code>table</code>,<code>success</code>,<code>error</code> and <code>warning</code>:

```php	
	//Using Facade
	Inspector::log(["description"], $myVar);
	Inspector::info(["description"], $myVar);
	Inspector::error(["description"], $myVar);
	Inspector::table(["description"], $myVar);
	Ispector::warning($myObj);
		
	//Using helper functions
	inspector()->info(...);
	inspector()->warning($myVar);
	
	// "li" is an alias of "inspector"
	li()->warning(...);
	li()->group('myGroup');
	
	// inspect
	inspect($var1, $var2, $var3, ...);
	
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
In addition to the ability to group information, each group (and subgroup) excecution time will be measured and shown.

### Conditionals
```php	
	inspector()->if($count > 100)->warning('count', $count);
	
	Inspector::if($tooMuch)->dd();

```

### Dump and die

Dump the entire message bag and end the script.

```php	
	inspector()->dd();
	// adding some last-minute vars
	inspector::dd($var1, $var2, ...);
	
	// dd helper function
	idd();
	idd($var1, $var2,...)
	

```

### Additional Information 
Each response will include information of:

* Database queries, including param bindings, excecution time and surce code
* Exceptions
* Complete request information
* Data passed to views (in view responses)
* Previous inspections (in redirect responses)
* Session information
* Total allocated RAM and total script excecution time
* Configurable $_SERVER dump 
    
## License
Laravel Zoo Inspector is licensed under the MIT License.