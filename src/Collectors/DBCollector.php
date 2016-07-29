<?php 

namespace Lsrur\Inspector\Collectors;

class DBCollector extends BaseCollector
{
    public $title = 'SQLs';

	private $sql = ['time'=>0, 'count'=>0, 'items'=>[]];

	public function getScript()
	{
		//echo $this->sql['items'][0]['files'][0]['src'];
		//dd($this->sql);
		if(count($this->sql)==0) return "";
		$script = "console.groupCollapsed('SQL (COUNT:".$this->sql['count'].", TIME:".$this->sql['time']."ms)');"; 
		foreach ($this->sql['items'] as $item) 
		{
			$script .= "console.groupCollapsed('".substr($item['sql'],0,30)."... (".$item['time']."ms) ');";
			$script .= "console.log('%c".$item['sql']."','font-size:11px');";
			foreach ($item['files'] as $file) 
			{
				$script .= "console.groupCollapsed('".$file['fileName']." #".$file['line']."');";
				 $l=0;
                foreach (explode(PHP_EOL,$file['source']) as $line)
                {
                    if($l++==3)
                    {
                       $script .= "console.info('%c".$this->e($line)."','font-size:11px;background-color: #E6FAFF');";
                    } else {
                       $script .= "console.log('%c".$this->e($line)."','font-size:11px;');";
                     
                    }
                }
				$script .= 'console.groupEnd();';
			}
			$script .= 'console.groupEnd();';
		}
		$script .= 'console.groupEnd();';
        return $script;

	}

	public function getPreJson()
	{
		$result = $this->get();
		$this->removeSrc($result['items']);
		return $result;
	}

	public function get()
	{
		return $this->sql;
	}


	public function count()
	{
		return count($this->sql['items']);
	}

    public function b_addSql($item)
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

    	$this->sql['items'][] = ['sql'=>$query, 'time'=>$item->time, 'end'=> microtime(true),'connection'=>$item->connectionName,  'files'=>$files];
    }

}