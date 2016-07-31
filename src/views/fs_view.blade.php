<?php
$response = $collectors['Response']['items'];
// dd($response);
?>
@if($response['class']=='Illuminate\Http\JsonResponse')
<div class="container-fluid inspector-panel" id='panel-view'>
	<pre><code class="json">{{json_encode($response['payload'], JSON_PRETTY_PRINT) }}</code></pre>
</div>
@elseif($response['class']=='Illuminate\Http\Response' )
	<?php
		$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$url = str_replace('laravel_inspector=dump','laravel_inspector=off', $url);
	?>	
	<iframe  class="inspector-panel" id="panel-view" src="{{$url}}" style="margin:0; width:100%;  border:none; overflow:none;" ></iframe>
@endif

<script type="text/javascript">
	jQuery(document).ready(function() {
	    var height = $(window).height();
	    console.log(height);
	    $('#panel-view').css('height', height * 0.9 | 0);


	});

</script>



