# Laravel Zoo Inspector
Laravel 5.x package to inspect, debug and profile many aspects of your server side code using your browser's console.

Laravel Inspector can collect information of your objects, variables, database queries, request data, views, session, etc. and will automatically output it to yours browsers console,	whatever your code returns views or ajax responses.

### Installation
	composer require lsrur/inspector


### Configuration
After updating composer, add the ServiceProvider to the providers array in config/app.php:

	Lsrur\Inspector\InspectorServiceProvider::class,

  
And this Facade in the same configuration file:

	'Inspector' => Lsrur\Inspector\Facade\Inspector::class,
	
	



## Sample Screenshot

![MacDown Screenshot](https://s31.postimg.org/vlfgyr21n/002.png)
  
## Usage
Laravel inspector will be activated if enviroment variable APP_DEBUG is true.  
You can turn it off temporarily (for the current request) with the following command:

	Inspector::turnOff();

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