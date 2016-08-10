<div class="container-fluid inspector-panel" id='panel-Session'>
	<h3>Session</h3>
	@if (isset($collectors['Session']) && isset($collectors['Session']['items']))
		@foreach($collectors['Session']['items'] as $key=>$value)
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
	@endif
</div>
