<div class="container-fluid inspector-panel" id='panel-phpinfo'>
	<div>
	<?php 
		ob_start();
		phpinfo(1 | 2 | 4 | 8  ) ;
		$result = ob_get_clean();
		$result = substr($result, strpos($result, '<body>')+6,-14);
		$result = str_replace('<table>','<table class="table table-striped">', $result);
	?>
	<style>
		.e {font-weight: bold}; 
	</style>
	{!! $result !!}
	</div>
</div>