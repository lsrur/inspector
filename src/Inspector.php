<?php

namespace Lsrur\Inspector;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

class Inspector
{

	private $collector;
	private $is_on = true;
	private $ajaxOutputFormat = 'script';

	public function __construct()
	{
		$this->collector = new Collector();
		$this->ajaxOutputFormat = \Config::get('inspector.ajax_output', 'script');
	}

	/**
	 * Show inspector full screen page and die
	 * @return [type] [description]
	 */
	public function dd()
	{
		if(request()->wantsJson())
		{
			header("Content-Type: application/json", true);
			$collectorData = $this->collector->getJson();
			echo json_encode(['LARAVEL_INSPECTOR'=>$collectorData]);
			die();

		} else {
			$collectorData = $this->collector->get();
			$view = (string)view('inspector::fullscreen', $collectorData);
			echo $view;
			die();
		}
	}
	
	/**
	 * Turn Inspector Off
	 * @return [type] [description]
	 */
	public function turnOff()
	{	
		$this->is_on = false;
	}

	/**
	 * InOn
	 * @return boolean 
	 */
	public function isOn()
	{
		return config('app.debug') && $this->is_on;
	}

	/**
	 * Show a table
	 * @param  [type] $p1 [description]
	 * @param  [type] $p2 [description]
	 * @return [type]     [description]
	 */
	public function table($p1, $p2=null)
	{

		$name = isset($p2) ? $p1 : $p2;
		$value = isset($p2) ? $p2 : $p1;

		$extra = ['count'=>count($value), 'size'=>tb()->formatMemSize(mb_strlen(json_encode($value)))];

		$this->collector->add('table', $name, $value, $extra);
	}
	
	/**
	 * Add an exception to collector's stack
	 * if configured as exception render, respond with fullscreen view (dd)
	 * @param [type] $e [description]
	 */
	public function addException($e)
	{
        $this->collector->addException($e);

        if( $this->isOn() && config('inspector.exception_render', false))
        {
			header('status: 500', true);
			if(in_array('getStatusCode', get_class_methods(get_class($e))))
				header('status:'.$e->getStatusCode(), true);

			$this->dd();
        }
	}    

	/**
	 * Conditional logging
	 * @param  bool   $condition [description]
	 * @return [type]            [description]
	 */
    public function if(bool $condition)
    {
    	$this->collector->condition = $condition===false ? $condition : null;
    
    	return $this;
    }

    /**
     * Magic methods
     * @param  [type] $method [description]
     * @param  [type] $args   [description]
     * @return [type]         [description]
     */
    public function __call($method, $args)
    {    
        if( in_array($method,['log', 'error', 'success', 'info', 'warning']))
        {
			array_unshift($args, $method);
	       	return call_user_func_array(array(&$this->collector, "add"), $args);
        }
        trigger_error("Method '$method' not found in Inspector class.", E_USER_ERROR);
    }

    /**
     * Add a group
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
	public function group($name)
	{
		$this->collector->group($name);
	}	

	/**
	 * End a group
	 * @return [type] [description]
	 */
	public function endGroup()
	{
		$this->collector->endGroup();
	}	
	
	/**
	 * Add sql from DB listener
	 * @param [type] $sql [description]
	 */
	public function addSql($sql)
    {
        $this->collector->addSql($sql);
    }

    /**
     * Determine the injector type
     * 
     * @param  [type] $request  [description]
     * @param  [type] $response [description]
     * @return [type]           [description]
     */
	protected function getInjectorType($request, $response)
	{
		if(tb()->isJsonRequest($request))
		{
			$this->injectorType = 'json';
		} elseif($response->isRedirect())
		{
			$this->injectorType = 'redirect';
		} elseif(is_object($response->getOriginalContent()) && get_class($response->getOriginalContent()) == 'Illuminate\View\View')
		{
			$this->injectorType = 'view';
		}

		return isset($this->injectorType);
	}


	/**
	 * Inject Inspector into Response
	 * @param  [type] $request  [description]
	 * @param  [type] $response [description]
	 * @return [type]           [description]
	 */
	public function injectInspection($request, $response)
	{
		$this->request = $request;
		$this->response = $response;
		$collectorData = $this->collector->get();
		
		if(!$this->getInjectorType($request, $response)) return;

		switch ($this->injectorType) {
			case 'redirect':
                $collectorData = $this->collector->get();
	   			$inspectionBag = (string)view('inspector::view', $collectorData, 
	   				[
			        'target'   =>  $response->getTargetUrl(),
	   				'title'=>'REDIRECT:'.$request->url().' >> '.$response->getTargetUrl()
	   				]);

                $request->session()->flash('LARAVEL_INSPECTOR_REDIRECT', $inspectionBag);
				break;
			case 'view':
				$collectorData = $this->collector->get();
		   		
	   			$inspectionBag = (string)view('inspector::view', $collectorData, 
	   				[
			        'viewData'   =>  $response->getOriginalContent()->getData(),
	   				'title'=>'VIEW:'.$response->getOriginalContent()->getName()
	   				]);
				// is redirection target?
				if($request->session()->has('LARAVEL_INSPECTOR_REDIRECT'))
	   			{
	   				// attach previous script 
	   				$inspectionBag = $request->session()->get('LARAVEL_INSPECTOR_REDIRECT').$inspectionBag;
	   			} 

	            $content = $response->getContent();

	            // Ensure string content
	            if(is_string($content))
	            {
					$content = str_replace('</body>', $inspectionBag, $response->getContent());
	            	$response->setContent($content);
	        	}
				break;
			
			case 'json':
				header('Content-Type: application/json');
				$collectorData = $this->collector->getJson();
	            $content = json_decode($this->response->getContent(), true) ?: [];
	
	           	$inspectionBag = $this->ajaxOutputFormat == 'json' ? $this->collector->getJson($collectorData) : 
	           		(string)view('inspector::debuginfo', $this->collector->get(), 
	   				['title'=>$request->getMethod().':'.request()->url(). ' STATUS:'.$response->status(), 'payload'=>$content]);

	            $content['LARAVEL_INSPECTOR'] = $inspectionBag; 
	            $response->setContent(json_encode($content));
				break;
		}
	}
}