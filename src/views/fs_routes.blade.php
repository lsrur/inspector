<div class="container-fluid inspector-panel" id='panel-Routes'>
	<h3>Application Routes</h3><br>
	<table class="table table-striped">
		<thead>
			<tr>
				<td><strong>Method</strong></td>
				<td><strong>Uri</strong></td>
				<td><strong>Name</strong></td>
				<td><strong>Action</strong></td>
				<td><strong>Middleware</strong></td>
			</tr>
		</thead>
		@if (isset($collectors['Routes']) && isset($collectors['Routes']['items']) )
			@foreach($collectors['Routes']['items'] as $route)
			<tr>
				<td>{{$route['method']}}</td>
				<td>{{$route['uri']}}</td>
				<td>{{$route['name']}}</td>
				<td>{{$route['action']}}</td>
				<td>{{$route['middleware']}}</td>
			</tr>
			@endforeach
		@endif
	</table> 

</div>
