<?php 

namespace Lsrur\Inspector\Collectors;

class RoutesCollector extends BaseCollector
{
	public $title = 'Routes';
	public $showCounter = false;
	private $routes = [];
	
	public function getScript()
	{
		return $this->genericToScript($this->routes);
	}
	
	public function __construct()
	{
		foreach (\Route::getRoutes() as $route)
		{
			
			$action = is_string($route->getAction()['uses']) ? $route->getAction()['uses'] : 'Closure';
			$name = isset($route->getAction()['as']) ? $route->getAction()['as'] : '';
			
			$this->routes[] = [
				'method'=> implode('|',$route->methods()),
				'name'	=> $name,
				'action'=> $action,
				'uri'	=> $route->uri(),
				'middleware' => isset($route->getAction()['middleware']) && is_array($route->getAction()['middleware']) ?
					implode(',',$route->getAction()['middleware']) : (isset($route->getAction()['middleware']) ? $route->getAction()['middleware'] : '')
				];
		}
	}

	public function getPreJson()
	{
		return $this->get();
	}

	public function count()
	{
		return count($this->routes);
	}

	public function get()
	{
		return $this->routes;
	}

}
