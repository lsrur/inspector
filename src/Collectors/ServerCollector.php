<?php 

namespace Lsrur\Inspector\Collectors;

class ServerCollector extends BaseCollector
{
	public $title = 'Server';
	public $showCounter = false;

	private $serverData;
	
	public function getScript()
	{
		return $this->genericToScript($this->serverData);
	}
	
	public function __construct()
	{
		$this->serverData = collect($_SERVER)->except(config('inspector.hide_server_keys',[]));
	}

	public function getPreJson()
	{
		return $this->get();
	}

	public function count()
	{
		return $this->serverData->count();
	}

	public function get()
	{
		return $this->serverData->toArray();
	}

}