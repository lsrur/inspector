<?php 
// add timestamp from laravel start time 
namespace Lsrur\Inspector\Collectors;

class TimerCollector extends BaseCollector
{
	public $title = 'Timers';
	public $showCounter = false;

	private $timers = [];

	public function getScript()
	{

		if(count($this->timers)==0) return "";

		$script = "console.groupCollapsed('TIMERS (".count($this->timers).")');";
		foreach ($this->timers as $key=>$value) 
		{
			$style = $value['type'] == 'timer' ? 'font-size:11px;line-height:1.8em;border-radius:3px;padding:3px 5px;color:white; background-color: #18BC9C' : 'font-size:11px;line-height:1.8em;border-radius:3px;padding:3px 5px;color:white; background-color: #3498DB';
			if($value['time']> 0)
			{
				$script .= "console.log('$key: {$value['time']}ms %c{$value['type']}','$style');";
				
			} else {
				$script .= $this->cl('log', $key, 'unfinished');	
			}
		}
		$script .= 'console.groupEnd();';
		return $script;
	}
	
	public function getPreJson()
	{

		return $this->get();
	}

	public function count()
	{
		return count($this->timers);
	}

	public function get()
	{
		return $this->timers;
	}

	public function b_timeEnd($timerName)
	{
		if(isset($this->timers[$timerName]) && $this->timers[$timerName]['type']=='timer')
			$this->timers[$timerName]['time'] = round( (microtime(true)  - $this->timers[$timerName]['start']) * 1000,2 );
	}

	public function b_time($timerName)
	{
		$this->timers[$timerName] = ['start'=>microtime(true), 'time'=>0, 'type'=>'timer'];		
	}

	public function b_timeStamp($name)
	{
		$this->timers[$name] = ['start'=>microtime(true), 'time'=>round((microtime(true)-LARAVEL_START)*1000,2), 'type'=>'timestamp'];		
	}

}