<div class="container-fluid inspector-panel" id='panel-server'>
	<h3>$_SERVER</h3><br>
	<table class="table table-striped">
	@foreach($server as $key=>$value)
		<tr>
			<td><strong>{{$key}}</strong></td>
			<td>{{$value}}</td>
		</tr>
	@endforeach
	</table>
</div>