<div class="container-fluid inspector-panel" id='panel-request'>
	<h3>{{$request['URL']}}</h3>
	<div style="border:1px solid #ddd; box-shadow: 0px 0px 2px #ccc;">
		<div style="border-bottom:1px solid #ddd; padding:5px">
			<strong>Input</strong>
		</div>
		<div>
			<pre style="background: #fff; color: #c7254e; font-size:15px;border:0">{!! tb()->getDump($request['INPUT']) !!}</pre>
		</div>
	</div>
	<br>
	<div style="border:1px solid #ddd; box-shadow: 0px 0px 2px #ccc;">
		<div style="border-bottom:1px solid #ddd; padding:5px">
			<strong>Headers</strong>
		</div>
		<div>
			<pre style="background: #fff; color: #c7254e; font-size:15px;border:0">{!! tb()->getDump($request['HEADERS']) !!}</pre>
		</div>
	</div>
</div>