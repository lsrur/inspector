<?php 
if(!is_array($data[0])) return;
$keys = array_keys($data[0]);$counter=0;
?>
<div class="table-panel" style="max-height:332px; overflow-y: auto">
<table class="table table-striped" style="font-size: 14px">
	<tr>
	<td style="color:#bcbcbc">#/{{count($data)}}</td>
	@foreach($keys as $key)
		<td><strong>{{$key}}</strong></td>
	@endforeach
	</tr>
	@foreach($data as $item)
	<tr>
		<td style="color:#bcbcbc">{{++$counter}}</td>
		@foreach($keys as $key)
			<td>
				@if(is_array($item[$key]))
				<a href="" class="table-array-link" data-toggle="modal" data-title="{{$key}}" data-code="{{print_r($item[$key])}}" data-target="#table-modal">
				  (Array)
				</a>
				@else
					{{$item[$key]}}
				@endif
			</td>
		@endforeach
	</tr>
	@endforeach
</table>
</div>