<div class="container-fluid inspector-panel" id='panel-Server'>
	<h3>$_SERVER</h3><br>
	<table class="table table-striped">
	@if (isset($collectors['Server']) && isset($collectors['Server']['items']))
		@foreach($collectors['Server']['items'] as $key=>$value)
			<tr>
				<td><strong>{{$key}}</strong></td>
				<td>{{$value}}</td>
			</tr>
		@endforeach
	@endif
	</table>
</div>
