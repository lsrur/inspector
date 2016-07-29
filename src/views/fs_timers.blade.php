<?php
	$start = LARAVEL_START;
	$total = $time;//round((microtime(true)-LARAVEL_START)*1000,2);
	$end = LARAVEL_START + $time * 1000;
	$lines = [
		['title'=>'Total Request Time', 'start'=>$start, 'duration'=>$total, 'type'=>'app'],
		['title'=>'Laravel Booted', 'start'=>LARAVEL_BOOTED, 'duration'=>round($total-(LARAVEL_BOOTED*1000),2), 'type'=>'timestamp'],
	];

	foreach ($collectors['Timers']['items'] as $key=>$value)
	{
		$lines[] = [
			'title' => $key,
			'start' => $value['start'],
			'duration' => $value['time'],
			'type'=>$value['type']
			];
	}

	foreach ($collectors['SQLs']['items']['items'] as $key=>$value)
	{
		$lines[] = [
			'title' => substr($value['sql'],0,50),
			'start'    => ($value['end'] - ($value['time']/1000)),
			'duration' => $value['time'],
			'type'=>'sql'
			];
	}

	for($i=0; $i<count($lines); $i++)
	{
		$st = round( ($lines[$i]['start'] - $start) * 1000, 2);
		$lines[$i]['p_start'] = round($st * 100 / $total,2) ;

		$lines[$i]['start'] = round(($lines[$i]['start'] - $start) * 1000 ,2) ;

		$end = round($st + $lines[$i]['duration'],2 );
		$lines[$i]['p_end'] = $end;
		$lines[$i]['p_dur'] = round(($end-$st) * 100 / $total,2);
	}
?>
<div class="container-fluid inspector-panel" id='panel-Timers'>
	<h3>Timeline <small>(experimental)</small></h3><br>
	<table class="table table-bordered">
		<tr>
			<td>&nbsp;</td>
			<td>
				<table border="0" style=" border-spacing: 0px;width:100%" class="tables">
					<tr>
					@for($i=0;$i<intval($total/($total/10));$i++)
						<?php 
							$tick=round($i*$total/10,0).'ms'; 
							$tick = strlen($tick)==3 ? $tick = $tick.'&nbsp;&nbsp;' : $tick;
						?>
						<td><div style="height: 10px">{{$tick}}</div>&#9662;</td>
					@endfor
					</tr>
				</table>
			</td>
		</tr>

		@foreach($lines as $timer)
		<tr>
			<td style="width: 200px">
			{{$timer['title']}}
			</td>
			<td>
				@if($timer['type']=='timestamp')
				<div class="timestamp" style="margin-left: {{ $timer['p_start'] }}%;">&nbsp;</div>
				@elseif($timer['type']=='sql')
					@if($timer['p_dur']<1)
						<div class="timestamp" style="margin-left: {{ $timer['p_start'] }}%;">&nbsp;</div>
					@else
						<div style="margin-left: {{ $timer['p_start'] }}%; width: {{ $timer['p_dur'] }}%;" class="timeline-{{$timer['type']}}">
							{{$timer['duration']}}ms
						</div>
					@endif
				@else
				<div style="margin-left: {{ $timer['p_start'] }}%; width: {{ $timer['p_dur'] }}%;" class="timeline-{{$timer['type']}}">
					{{$timer['duration']}}ms
				</div>
				@endif
			</td>
		</tr>	
		@endforeach		

	</table>	
</div>