<?php
$exceptions = $collectors['Exceptions']['items'];$counter=1;
?>
<div class="container-fluid inspector-panel" id='panel-Exceptions'>
	<div class="row">
		@foreach($exceptions as $exception)
			<div class="col-lg-12">
				@if($counter>1)
					<div style="height: 20px; ">&nbsp;</div>
				@endif  
				<div>
					<div style="margin-left: 0px; border: 1px solid #ddd; padding:10px;box-shadow: 0px 0px 2px #ccc;">
						<h4 style="padding-bottom:0px; margin: 0px">
							<span class="pull-right" style="border-radius: 3px; font-size:13px;text-align;color:white;center;background-color: #F4645F; padding: 2px 6px">
								{{$counter.'/'.count($exceptions)}}
							</span>
							<strong>{{$exception['class']}}</strong>
							@if($exception['caught'])
								<span style="margin-left:5px;font-size:11px; position:relative; top:-2px" class="label label-success">CAUGHT</span>
							@else
								<span style="margin-left:5px;font-size:11px; position:relative; top:-2px"class="label label-danger">UNCAUGHT</span>
							@endif
						</h4>
						<h3 style="margin-bottom: 15px">
							{{$exception['message']}}
							@if(isset($exception['code']) && $exception['code']!='0')
								({{$exception['code']}})
							@endif
						</h3>
						@foreach($exception['files'] as $file)
							<h4>{{ $file['fileName']}} #{{$file['line'] }}
								@if(isset($file['tag']) && $file['tag']=='my code')
									<span style="margin-left:5px;font-size:11px;position: relative; top: -2px" class="label label-danger">MY CODE</span>
								@endif
								@if(isset($file['tag']) && $file['tag']=='vendor')
									<span style="margin-left:5px;font-size:11px;position: relative; top: -2px" class="label label-warning">VENDOR</span>
								@endif
							</h4>
							@if($file['src'] != '')
								<pre>{!! $file['src'] !!}</pre>
							@endif
						@endforeach
						<h5><a href='javascript:void(0)' class="trace-link" style="text-decoration: none" id="{{$counter}}">FULL TRACE...</a></h5>
						<div style="display: none" id='trace-panel-{{$counter}}'>
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
						</div>
					</div>
				</div>
			</div>
			<?php $counter++;?>
		@endforeach	
	</div>
</div>
<script type="text/javascript">
 $('.trace-link').click(function(e){
 	var panel = '#trace-panel-'+$(this).attr('id');
 	console.log(panel);
 	if($(panel).is(':visible'))
 	{
 		$(panel).fadeOut(100);
 	} else {
 		$(panel).slideDown(500);
 	}
 });
</script>
