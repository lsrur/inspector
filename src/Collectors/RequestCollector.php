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
		if(\App::runningInConsole())
		{
			$this->request = [];
		} else {
			$this->request = [
				'url'		=> request()->url(),
				'method'	=> request()->method(),
				'input'		=> request()->input(),
				'action'	=> \Route::getCurrentRoute() !== null ? \Route::getCurrentRoute()->getAction() : null,
				'headers'	=> request()->header(),
			];
		}
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