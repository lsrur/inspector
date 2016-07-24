<div class="container-fluid inspector-panel" id='panel-session'>
	<h3>Session</h3>

	@foreach($session as $key=>$value)
	<div style="border:1px solid #ddd; box-shadow: 0px 0px 2px #ccc;">
		<div style="border-bottom:1px solid #ddd; padding:5px">
			<strong>{{$key}}</strong>
		</div>
		<div>
			<pre style="background: #fff; color: #c7254e; font-size:15px;border:0">{!! inspector()->getDump($value) !!}</pre>
		</div>
	</div>
	<br>
	@endforeach
</div>