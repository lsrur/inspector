<?php

namespace Lsrur\Inspector;

class Collector
{
	private $stack = [];
	public $condition;
	private $sql = ['items'=>[], 'time'=>0, 'count'=>0];
	public $exception;


	public function getJson()
	{
        $result = []; $group='';
     	$collectorData = $this->get();
        foreach ($collectorData['debug'] as $item) {
        	switch ($item['style']) {
        		case 'group':
					$group = $group=='' ? $item['name'] : $group.'.'.$item['name'];
                	array_set($result, $group.'.profile', round( ($item['end'] - $item['start']) * 1000,2 ).'ms');
        			break;
        		case 'endgroup':
        			$group = substr($group,0, strrpos($group, '.'));
        			break;
        		default:
        			$keyName = $group == '' ? '' : $group.'.';
        			$keyName .= str_slug($item['name'],'_');
               		if(isset($item['value']))
                    	array_set($result, $keyName, $item['value']);
        			break;
        	}
        }
        $collectorData['debug'] = $result;
        return $collectorData;
	}

	/**
	 * Return internal stack
	 * @return array
	 */
	public function get()
	{

		return [
			'time'		 => round((microtime(true)-LARAVEL_START)*1000,2),
	        'allocRam'   => tb()->formatMemSize(memory_get_usage()),
	        'debug'       => $this->stack,
	        'response'	 => response(),
	        'request'    => [
	            'URL' => request()->url(),
	            'INPUT' => request()->all(), 
	            'HEADERS' => request()->header()
	            ], 
	        'server'     => collect($_SERVER)->except(config('inspector.hide_server_keys',[])),
	        'session'    => session()->all(),
	        'exception'  => $this->exception, 
	        'sql'        => $this->sql,
            'messageCount' => collect($this->stack)
                ->whereIn('style', ['log', 'info', 'success', 'table', 'error', 'warning'])
                ->count()
	    ];
	}

	/**
	 * Start a group
	 * @param  [type] $name 
	 * @return [type]       
	 */
    public function group($name)
    {
        $this->stack[] = ['name'=>$name, 'style'=>'group', 'start'=>microtime(true), 'end'=>0, 'time'=>0];
    }

    /**
     * End a group
     * @return [type] 
     */
    public function endGroup()
    {
        $end = microtime(true);
        for($i=count($this->stack)-1; $i>=0; $i--)
        {
            if($this->stack[$i]['style']=='group' && $this->stack[$i]['end']==0)
            {
            	$this->stack[$i]['end'] = $end;
            	$time = round( ($this->stack[$i]['end'] - $this->stack[$i]['start']) * 1000,2 );
                $this->stack[$i]['time'] = $time;
            	unset($this->stack[$i]['start']);
                $name = $this->stack[$i]['name'];
            	break;
            }
        }
        if(isset($name))
            $this->stack[] = ['style'=>'endgroup', 'name'=>$name, 'time'=>$time];
    }

    public function addSql($item)
    {

    	$query = $item->sql;
	 	foreach ($item->bindings as $value) 
	    {	        	
	    	$query = preg_replace('/\?/', $value, $query, 1);
	    }
	       
    	$this->sql['time'] += $item->time;
    	$this->sql['count']++;
        $files=[];
        foreach (debug_backtrace() as $trace) {
            if(isset($trace['file']) && str_is(app_path().'*', $trace['file']))
                $files[] = ['file'=>$trace['file'],'line'=>$trace['line']];
        }
        $files = $this->getSourceCode($files);

    	$this->sql['items'][] = ['sql'=>$query, 'time'=>$item->time, 'connection'=>$item->connectionName,  'files'=>$files];
    }

    /**
     * Add inspection
     * @param [type]  $style      
     * @param [type]  $p1         
     * @param [type]  $p2         
     * @param array   $extra      
     * @param integer $traceSteps 
     */
    public function add($style, $p1, $p2=null, $extra=[], $traceSteps = 4 )
    {
    	
    	if($this->condition === false) 
    	{
        	$this->condition = null;
    		return;
    	}

 		$extra['trace'] = $this->getTrace($traceSteps);
 		$name = isset($p2) ? $p1 : $p2;
		$value = isset($p2) ? $p2 : $p1;
        $name = $name ?? ' ';
        $this->stack[] = array_merge(array_filter(['name'=>$name,'value'=>$value, 'style'=>$style]), $extra);

        $this->condition = null;
    }

    /**
     * Return file and line number
     * @param  integer $steps 
     * @return string        
     */
    private function getTrace($steps = 3)
    {
        if(!isset(debug_backtrace()[$steps]['file'])) $steps--;
        $file = collect(explode('/', debug_backtrace()[$steps]['file']))->last();
        return $file." #".debug_backtrace()[$steps]['line'];
    }

    public function dd()
    {
    	dd($this->stack);
    }

    public function addException($exception)
    {

        $files = [['file'=>$exception->getFile(), 'line'=>$exception->getLine()]];
        
        foreach ($exception->getTrace() as $item) {
            if(isset($item['file']) && str_is(app_path().'*', $item['file']))
                $files[] = ['file'=>$item['file'],'line'=>$item['line']];
        }
        $files = $this->getSourceCode( collect($files)->unique('file')->toArray() );

        $this->exception = [ 
            'message' => $exception->getMessage(),
            'code'    => $exception->getCode(),
            'trace'   => $exception->getTrace(),
            'class'   => get_class($exception),
            'files'   => $files
            ];
    
    }

    private function getSourceCode($files)
    {
        
        for($j=0;$j<count($files);$j++)
        {
            $src=[];
            if(isset($files[$j]['file']))
            {
                $sourceFile = $files[$j]['file'];
                $fromLine = $files[$j]['line'] - 3;
                $toLine = $fromLine + 6;
                $i=0;

                $handle = fopen($sourceFile, "r");
                if ($handle) {
                    $src[] = '<?php'.PHP_EOL;

                    while (($line = fgets($handle)) !== false) 
                    {
                        $i++;
                        if($i>=$fromLine && $i<=$toLine)
                            if($i == $files[$j]['line'])
                            {
                                $src [] = '-@'.$i.':'.substr($line,0,-1).'@-';
                            } else {
                                $src [] = $i.':'.$line;
                            }
                    }
                    fclose($handle);

                    $src = highlight_string(implode("",$src), true);
                    $src = str_replace('-@', '<div style="background-color:#FFDFD8 !important">', $src);
                    $src = str_replace('@-', '</div>', $src);
                    $src = str_replace('&lt;?php<br />', '', $src);
                    $src = str_replace('\n', '', $src);
                    $files[$j]['src'] = $src;
                    $files[$j]['fileName'] = '..'.substr($files[$j]['file'],strlen(base_path()));
                    $files[$j]['tag'] = strpos($files[$j]['file'], app_path()) === false ? 'vendor' : 'mine';
                }
            }        
        } 

        return $files;
    }


} // END CLASS