<?php

    try {
?>
<div class="container-fluid inspector-panel" id='panel-debug'>
    <div class="row">
        <div class="col-lg-12">
			<?php $group=0?>
        	@foreach($debug as $item)
            	@if($item['style'] == 'endgroup')
            		<?php $group-- ?>
            		<span style="font-size:13px;padding:3px 20px 3px 10px;position: relative; left:-2px; color:white;background-color:#F4645F;border-bottom: 1px solid #F4645F">
                        End {{$item['name']}} ({{$item['time']}}ms)   
                    </span>
            		</div>
            	@endif
            	@if($item['style'] == 'group')
            		<?php $dl=$group*20;  ?>
            		<div style="font-size:13px; margin-left: {{$dl}}px;border-left: 2px solid #F4645F; margin-bottom: 12px">

            			<span style="margin-bottom:10px; padding:3px 20px 3px 10px;background: #F4645F; color:white; position: relative; top:2px">{{$item['name']}} ({{$item['time']}}ms)</span>
            			<div style="height: 10px">&nbsp;</div>
        			<?php $group++?>
					
            	@endif
				@if(in_array($item['style'], ['table','info','error','log', 'warning', 'success']))
					<?php
                        $dl=0; 
                        $panelStyle = $item['style']; 
                        if($panelStyle=='error') $panelStyle='danger';
                        if($panelStyle=='log') $panelStyle='default';

                    ?>
                    <div style="margin-left:10px; border: 1px solid #ddd; margin-bottom:10px;box-shadow: 0px 0px 2px #ccc;">
                      <div style="padding:5px 10px;border-bottom: 1px solid #ddd ">
                            <strong>{{$item['name']}} ({{$item['trace']}})</strong>
                            @if(in_array($item['style'], ['info', 'warning', 'error', 'success']))
                                <span class="label label-{{$panelStyle}}" style="position:relative; top:-1px">{{strtoupper($item['style'])}}</span>
                            @endif  
                      </div>
                      <div class="panel-body">
                          @if($item['style']=='table' && is_array($item['value']))
                            <?php $item['value'] = json_decode(json_encode($item['value']),true);?>
                            @if(count($item['value']) > 0)
                            <table class="table table-striped" style="font-size:15px">
                              <thead>
                                <tr>
                                  <th><?php echo implode('</th><th>', array_keys(current($item['value']))); ?></th>
                                </tr>
                              </thead>
                              <tbody>
                            <?php foreach($item['value'] as $row): array_map('serialize', $row); ?>
                                <tr>
                                  <?php $row=array_values(array_map('serialize', $row));  ?>
                                  <td><?php echo implode('</td><td>', $row); ?></td>
                                </tr>
                            <?php endforeach; ?>
                              </tbody>
                            </table>
                            @endif
                          @else
                              {!!tb()->getDump($item['value'])!!}
                          @endif
                      </div>
                    </div>
                @endif
			 		
        	@endforeach
        	@for($i=0;$i<$group;$i++)
				</div>
     		@endfor
        </div>
          
    </div>
</div>
<?php 
} catch (Exception $e) {
    dump($e);
    die();
    }

    ?>
