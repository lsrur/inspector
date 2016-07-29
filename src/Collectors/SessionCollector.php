<?php 

namespace Lsrur\Inspector\Collectors;

class SessionCollector extends BaseCollector
{
	public $title = 'Session';
	public $showCounter = false;
	
	public function getScript()
	{
		return $this->genericToScript(\Session::all());
	}
	
	public function getPreJson()
	{
		return $this->get();
	}

	public function count()
	{
		return count(\Session::all());
	}

	public function get()
	{
		return \Session::all();
	}

}