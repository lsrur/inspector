<?php $groupCount=0; $payloadSize=0;

?>
 	console.groupCollapsed('LARAVEL INSPECTOR {!!$title!!}, RAM:{{$allocRam}}, TIME:{{round((microtime(true)-LARAVEL_START)*1000,2)}}ms');
 	@if(isset($payload))
 		<?php 
 		$payLog = '';
 		foreach($payload as $key=>$val)
 		{
 			$jsonValue = json_encode($val);
 			$payLog .= "console.log('$key', $jsonValue);";
 			$payloadSize += mb_strlen($jsonValue);
 		}?>
 		console.groupCollapsed('PAYLOAD ({{$payloadSize}} Bytes)');
		{!!$payLog!!} 		
 		console.groupEnd();
 	@endif
 	@if(isset($exception))
 		console.groupCollapsed("EXCEPTION: {!!$exception->getMessage()!!}, {!! $exception->getFile()!!} #{!! $exception->getLine()!!}");
 		console.log("TRACE", {!!json_encode($exception->getTrace()) !!});
 		console.groupEnd();
 	@endif
	@if(count($viewData))
 		console.groupCollapsed('DATA PASSED TO VIEW');
		@foreach($viewData as $key=>$value)
		console.log('{{$key}}', {!! json_encode($value)!!});
		@endforeach
		console.groupEnd();
	@endif
	
	@if(count($data)>0)
	console.groupCollapsed('DEBUG');
	@foreach($data as $item)
		@if(in_array($item['style'], ['log', 'info',  'error', 'table']))
			@if(isset($item['name']))
				console.{{$item['style']}}("{{$item['name'].': ' }}", {!!$item['value']!!});
			@else
				console.{{$item['style']}}({!!$item['value']!!});
			@endif
		@elseif($item['style'] == 'group')
			<?php 
				$time = $item['end'] > 0 ? ''.round( ($item['end'] - $item['start']) * 1000,2 ).'ms' : '';
			?>
			console.groupCollapsed('{{$item['name']}}', '{{$time}}');
			<?php $groupCount++; ?>
		@elseif($item['style'] == 'endgroup')
			console.groupEnd();
            <?php $groupCount--; ?>
		@endif
	@endforeach
 	console.groupEnd();
	@endif

	@for($i=0; $i<$groupCount; $i++)
    	console.groupEnd();
    @endfor

	@if(count($sql) > 0)
	    console.groupCollapsed('SQL ({{count($sql)}})');
	    <?php $total = 0; ?>
	    @foreach ($sql as $item)  
	        <?php
	        $query = $item->sql;
	        foreach ($item->bindings as $value) 
	        {	        	
	            $query = preg_replace('/\?/', $value, $query, 1);
	        }
	        ?>
	        <?php $total += $item->time; ?>
	        console.groupCollapsed('{!!substr($query, 0,40)!!}... ({!!$item->time!!}ms)');
	        console.log('{!!$query!!}');            
	        console.groupEnd();  
	    @endforeach
	    console.info('DB TOTAL TIME: {{$total}}ms');
	    console.groupEnd();
	@endif

    @if(null !== request()->route())
	    console.groupCollapsed('REQUEST');

    	console.log('URL: {{request()->url()}}');
        console.log('ROUTE: {!!request()->route()->getPath().' ('.request()->route()->getName().') -> '.request()->route()->getActionName()!!}');
        console.log('INPUT:',{!!json_encode(request()->all())!!});
    	console.log('HEADERS:',  {!! json_encode(request()->header())!!});

        console.groupEnd();
    @endif

	console.groupCollapsed('SERVER');
    console.log({!!json_encode(collect($_SERVER)->except(config('inspector.hide_server_keys')))!!});
    console.groupEnd();    

    console.groupCollapsed('SESSION');
    console.log({!!json_encode(session()->all())!!});
    console.groupEnd();      
    console.groupEnd(); 
