<?php

namespace Lsrur\Inspector;

class CollectorManager
{

	private $collectors = [];
	private $collectorMethods = [];
	private $owner;

	public function __construct($owner)
	{
		$this->owner = $owner;

		$availabelCollectors = array_merge([ 
			'ExceptionCollector'	=> ['inspector'=>true, 'fullscreen'=>true],
			'MessageCollector' 	 	=> ['inspector'=>true, 'fullscreen'=>true],
			'DBCollector'			=> ['inspector'=>true, 'fullscreen'=>true],	
			'ServerCollector'		=> ['inspector'=>true, 'fullscreen'=>true],
			'SessionCollector'		=> ['inspector'=>true, 'fullscreen'=>true],
			'RequestCollector'		=> ['inspector'=>true, 'fullscreen'=>true],
			'ResponseCollector'		=> ['inspector'=>true, 'fullscreen'=>true],
			'RoutesCollector'		=> ['inspector'=>false, 'fullscreen'=>true],
			'TimerCollector'		=> ['inspector'=>true, 'fullscreen'=>true],
			], \Config::get('inspector.collectors',[]));

		$this->collectors = collect($availabelCollectors)->map(function($item, $key){
			$class = "\\Lsrur\Inspector\\Collectors\\".$key;
			if(class_exists( $class ))
			{
				// Extract public methods starting with 'b_' from collector classes
				collect(get_class_methods($class))->each(function($item) use($key){
					if(strpos($item,'b_')===0)
						$this->collectorMethods[substr($item,2)]= $key;
				})->filter()->toArray();

				return array_merge($item, ['obj'=>new $class()]);
			}
		})->filter()->toArray();

	}

	public function get($collectorName)
	{
		if(isset($this->collectors[$collectorName]))
			return $this->collectors[$collectorName]['obj'];
	}

	public function getMethod($methodName)
	{
		return isset($this->collectorMethods[$methodName]) ? 
			$this->get($this->collectorMethods[$methodName]) : false;
	}

	public function getScripts($outputType, $title, $statusCode)
	{
		
		$scriptTime = round((microtime(true)-LARAVEL_START)*1000,2);
		$scriptRAM = formatMemSize(memory_get_usage());
		
		$statusStyle = 'font-size:11px;border-radius:3px;padding:1px 4px;color:white; background-color: #27AE60';
		if($statusCode >= 400 && $statusCode < 500)
		{
			$statusStyle = 'font-size:11px;border-radius:3px;padding:1px 4px;color:white; background-color: #F39C12';
		} elseif ($statusCode>=500) {
			$statusStyle = 'font-size:11px;border-radius:3px;padding:1px 4px;color:white; background-color: #E74C3C';
		}
		$script= "console.groupCollapsed('%cLaravel Inspector%c $title (TIME:{$scriptTime}ms, RAM:$scriptRAM) %c $statusCode ',
			'line-height:1.8em;padding:2px 8px;font-size:12px; border-radius:3px; color:white;background:#F46661',
			'background-color:white', '$statusStyle');";
		foreach ($this->collectors as $collector) 
		{
			if($collector[$outputType] && $collector['obj']->count() >0 )
				$script .= $collector['obj']->getScript();
		}

		$script .= 'console.groupEnd();';

		return $script;
	}

	public function getPreJson($outputType)
	{
		$result = [];
		foreach ($this->collectors as $collector) 
		{
			if($collector[$outputType])
			{
				$result[$collector['obj']->title] = $collector['obj']->getPreJson();
			}
		}
		return $result;
	}
	
	public function getFs()
	{
		$result = [];
		foreach ($this->collectors as $collector) 
			if($collector['fullscreen'])
				$result[$collector['obj']->title] = [
					'showCounter' => $collector['obj']->showCounter,
					'count'=> $collector['obj']->count(), 
					'items'=>$collector['obj']->get()];
			
		return $result;
	}	

	public function getRaw()
	{
		$result = [];
		foreach ($this->collectors as $collector) 
				$result[$collector['obj']->title] = $collector['obj']->get();
			
		return $result;
	}

}
