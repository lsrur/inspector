<?php 
	$sql = $collectors['SQLs']['items'];
	$lastFileName = '';
	$i=0; 
?>
<div class="container-fluid inspector-panel" id='panel-SQLs'>
	@foreach($sql['items'] as $item)		
	<div style="border:1px solid #ddd; box-shadow: 0px 0px 2px #ccc;">
		<div style="border-bottom:1px solid #ddd; padding:5px">
			<?php $i++;?>
			<span class="badge badge-info">{{$i}}</span>&nbsp; 
			<code>{{$item['time']}}ms {{round($item['time']*100/$sql['time'],2)}}%</code>
			<span class="label label-info pull-right" style="font-size:14px">{{$item['connection'] }}</span>
		</div>
		
		<div>	
			<div style="padding:10px">
				<code style="background: #fff; color: #c7254e; font-size:14px; line-height: 1.6em; border:0">{{$item['sql']}}</code>
			</div>
			<div style="padding:5px; border-top:1px solid #ddd;background-color: #fafafa" >
				@if(count($item['files']))
				<?php  $file=$item['files'][0] ?>			
					<strong>{{$file['fileName']}} #{{$file['line']}}</strong><span>
					&nbsp;
					@if(isset($file['tag']) && $file['tag']=='my code')
						<span style="margin-left:5px;font-size:11px;position: relative; top: -1px" class="label label-danger">MY CODE</span>
					@endif
					@if(isset($file['tag']) && $file['tag']=='vendor')
						<span style="margin-left:5px;font-size:11px;position: relative; top: -1px" class="label label-warning">VENDOR</span>
					@endif
					@if($lastFileName != $file['fileName'].$file['line'])	
						<pre style="background: #fafafa; border:0">{!! $file['src'] !!}</pre>	
					<?php $lastFileName = $file['fileName'].$file['line']; ?>	
					@endif
				@endif
			</div>	
		</div>

	</div>
	<br>

	@endforeach

</div>
