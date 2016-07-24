<div class="container-fluid inspector-panel" id='panel-exception'>
    <div class="row">
        <div class="col-lg-12">
        	<div class="panels">
	        	<h4 style="border-bottom: 1px solid #ddd; padding-bottom:5px"><strong>{{$exception['class']}}</strong></h4>
				<h3 style="margin-bottom: 15px">{{$exception['message']}} 
				
				</h3>
				@foreach($exception['files'] as $file)
					<h4>{{ $file['fileName']}} #{{$file['line'] }}
						@if(isset($file['tag']) && $file['tag']=='mine')
							<span style="font-size:11px;position: relative; top: -2px" class="label label-danger">MY CODE</span>
						@endif
						@if(isset($file['tag']) && $file['tag']=='vendor')
							<span style="font-size:11px;position: relative; top: -2px" class="label label-warning">VENDOR CODE</span>
						@endif
					</h4>
					
					@if($file['src'] != '')
						<pre>{!! $file['src'] !!}</pre>
						
					@endif
				@endforeach
			</div>
			<br>
			<h4>Trace</h4>
			@foreach($exception['trace'] as $item)
					

				<div style="padding:  7px 0px; border-top: 1px solid #ddd">
					@if(isset($item['file']))
						<h5>File: <code>{{$item['file']}} #{{$item['line']}}</code></h5>
					@endif
					<h5>Function: <code>{{$item['function'] or ''}}</code></h5>
					@if(isset($item['class']))
					<h5>Class: <code>{{$item['class'] or ''}}</code></h5>	
					@endif
				
				
				</div>
		
			@endforeach
			<br>
			<br>

        </div>
    </div>
</div>
