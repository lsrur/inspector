<?php 

namespace Lsrur\Inspector\Collectors;

class ResponseCollector extends BaseCollector
{
	public $title = 'Response';
	public $showCounter = false;

	private $responseData;
	
	public function getScript()
	{
		$this->get();
		return $this->genericToScript($this->responseData);
	}
	
	public function getPreJson()
	{

		return $this->get();
	}

	public function count()
	{
		return count($this->get());
	}

	public function get()
	{
		$this->responseData = [];
		$resp = app('Inspector')->getResponse();
		if(isset($resp))
		{
			$this->responseData['status'] = $resp->status();
	//		$this->responseData['headers'] = $resp->headers->all();
			$this->responseData['class'] = get_class($resp); 
					$this->responseData['size'] = formatMemSize(strlen($resp->getContent()));

			if(get_class($resp)=="Illuminate\Http\Response")
			{	
				if(is_object($resp->getOriginalContent()) && get_class( $resp->getOriginalContent() ) == 'Illuminate\View\View')
				{
					$this->responseData['view'] = $resp->getOriginalContent()->getName();
					$this->responseData['dataPassedToView'] =$resp->getOriginalContent()->getData();
					$this->responseData['size'] = formatMemSize(strlen($resp->getContent()));					
				} 
			}
		}
		return $this->responseData;
	}

}