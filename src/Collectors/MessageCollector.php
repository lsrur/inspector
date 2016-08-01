<?php 

namespace Lsrur\Inspector\Collectors;

class MessageCollector extends BaseCollector
{
    public $title = 'Messages';
	private $stack = [];

    /**
     * @return int
     */
	public function count()
	{
		return count($this->stack);
	}

    /**
     * @return string
     */
	public function getScript()
	{
		$script = "console.group('MESSAGES');"; 
		$groupCount=0;
		foreach ($this->stack as $item) 
		{
			$name = isset($item['name']) ? $item['name'] : null; 
			if(isset($item['trace'])) $name .= ' ('.$item['trace'].')';
			$name= trim($name);
			if(in_array($item['style'], ['info','log','error', 'warning', 'success']))
			{
				$script .= $this->cl($item['style'], $name, $item['value']);	
			}  elseif($item['style']=='table') {
				$script .= $this->clGroup($name);
				$script .= $this->clTable($item['value']);
				$script .= $this->clGroupEnd();
			} elseif($item['style']=='group') {
				$script .= $this->clGroup($name.' ('.$item['time'].'ms)');
				$groupCount++;
			} elseif($item['style']=='groupend') {
				$script .= $this->clGroupEnd();
				$groupCount--;
			}
		}
		// Close forgotten groups
		for($i=0; $i<$groupCount; $i++)
			$script .= $this->clGroupEnd();
		$script .= 'console.groupEnd();';
	
		return $script;
	}

    /**
     * @return Array
     */
	public function getPreJson()
	{
        $result = []; $group='';$g=0;
        foreach ($this->stack as $item) {
        	switch ($item['style']) {
        		case 'group':
                    $name = 'group_'.$item['name'];
					$group = $group=='' ? $name : $group.'.'.$name;
                    array_set($result, $group.'._time', $item['time']); 
        			break;
        		case 'groupend':
        			$group = substr($group,0, strrpos($group, '.'));
        			break;
        		default:
        			$keyName = $group == '' ? '' : $group.'.';
        			$name = $item['name'] ? $item['name'] : 'nn_'.(++$g);
        			$keyName .= str_slug($item['style'].'_'.$name,'_');
               		if(isset($item['value']))
                    	array_set($result, $keyName, $item['value']);
        			break;
        	}
        }

        return $result;
	}

	/**
	 * Return internal stack
	 * @return array
	 */
	public function get()
	{
		return $this->stack;
	}

	/**
	 * Start a group
	 * @param  [type] $name 
	 * @return [type]       
	 */
    public function b_group($name)
    {
        $this->stack[] = ['name'=>$name, 'style'=>'group', 'start'=>microtime(true), 'end'=>0, 'time'=>0];
    }

    /**
     * End a group
     * @return [type] 
     */
    public function b_groupEnd()
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
            $this->stack[] = ['style'=>'groupend', 'name'=>$name, 'time'=>$time];
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
 		$extra['trace'] = $this->getTrace($traceSteps);
        if(starts_with($extra['trace'], 'PhpEngine.php'))
            $extra['trace'] = 'BLADE';
 
 		$name = isset($p2) ? $p1 : $p2;
		$value = isset($p2) ? $p2 : $p1;
         
        if($style == 'table')
         {
            if(!is_array($value))
            {
                if(get_class($value) == "Illuminate\Pagination\LengthAwarePaginator")
                {
                    $value = $value->getCollection()->toArray();
                } elseif( in_array('toArray', get_class_methods(get_class($value)) ))
                {
                    $value = $value->toArray();
                } 
            }
            $value = json_decode(json_encode($value),true);
        }
        $name = isset($name) ? $name : '';
        $this->stack[] = array_merge(['name'=>$name,'value'=>$value, 'style'=>$style], $extra);     
    }

}