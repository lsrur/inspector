<?php 

namespace Lsrur\Inspector\Collectors;

class RequestCollector extends BaseCollector
{
	public $title = 'Request';
	public $showCounter = false;

	private $request;
	
	public function getScript()
	{
		return $this->genericToScript($this->request);
	}
	
	public function __construct()
	{
		$this->request = [
			'url'		=> request()->url(),
			'method'	=> request()->method(),
	//		'headers'	=> request()->header(),
			'input'		=> request()->input()
		];
	}

	public function getPreJson()
	{
		return $this->get();
	}

	public function count()
	{
		return count($this->request);
	}

	public function get()
	{
		return $this->request;
	}

}