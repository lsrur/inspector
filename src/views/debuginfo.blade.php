<?php $groupCount=0; $payloadSize=0;

?>
 	console.groupCollapsed('LARAVEL INSPECTOR {!!$title!!}, RAM:{{$allocRam}}, TIME:{{$time}}ms');
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
 		console.groupCollapsed("EXCEPTION: {!!$exception['message']!!}");
 		console.log("FILES", {!!json_encode($exception['files']) !!});
 		console.log("TRACE", {!!json_encode($exception['trace']) !!});
 		console.groupEnd();
 	@endif
	@if(isset($viewData) && count($viewData))
 		console.groupCollapsed('DATA PASSED TO VIEW ({{count($viewData)}})');
		@foreach($viewData as $key=>$value)
		console.log('{{$key}}:', {!! json_encode($value)!!});
		@endforeach
		console.groupEnd();
	@endif
	
	@if(isset($messages) && count($messages)>0)
	console.groupCollapsed('MESSAGES ({{$messageCount}})');
	@foreach($messages as $item)
		@if(in_array($item['style'], ['log', 'info',  'error']))
			console.{{$item['style']}}("{{$item['name'] or 'noname'}} ({{$item['trace']}})", {!!json_encode($item['value'])!!});
		@elseif($item['style'] == 'success')
			console.log("(success) {{$item['name'] or 'noname'}} ({{$item['trace']}})", {!!json_encode($item['value'])!!});
		@elseif($item['style'] == 'table')
			console.groupCollapsed("{{$item['name']}} ({{$item['trace']}}): Count:{{count($item['value'])}}, Size:{{$item['size']}}");
			console.table({!!json_encode($item['value'])!!});
			console.groupEnd();
		@elseif($item['style'] == 'group')
		
			console.groupCollapsed('{{$item['name']}}', '({{$item['time']}}ms)');
			<?php $groupCount++; ?>
		@elseif($item['style'] == 'endgroup')
			console.groupEnd();
            <?php $groupCount--; ?>
		@endif
	@endforeach
	@for($i=0; $i<$groupCount; $i++)
    	console.groupEnd();
	@endfor

 	console.groupEnd();
	@endif

	@if(count($sql['count']) > 0)
	    console.groupCollapsed('SQL ({{$sql['count']}}, {{$sql['time']}}ms)');
	    
	    @foreach ($sql['items'] as $item)  
	        console.groupCollapsed('{!!substr($item['sql'], 0,40)!!}... ({!!$item['time']!!}ms)');
	        console.log('{!!$item['sql']!!}');            
	        console.groupEnd();  
	    @endforeach
	    console.groupEnd();
	@endif

    console.groupCollapsed('REQUEST ({{count($request)}})');
    @foreach($request as $key=>$value)
    console.log("{{$key}}:", {!! json_encode($value)!!});
    @endforeach
    console.groupEnd();

	console.groupCollapsed('SERVER ({{count($server)}})')
    @foreach($server as $key=>$value)
    console.log("{{$key}}:", {!! json_encode($value)!!});
    @endforeach
    console.groupEnd();    

	console.groupCollapsed('SESSION ({{count($session)}})')
    @foreach($session as $key=>$value)
    console.log("{{$key}}:", {!! json_encode($value)!!});
    @endforeach
    console.groupEnd();    
console.groupEnd();    

