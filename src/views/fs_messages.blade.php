<?php $messages = $collectors['Messages']['items']; ?>
<div class="container-fluid inspector-panel" id='panel-Messages'>
    <div class="row">
        <div class="col-lg-12">
            <?php $group=0?>
            @foreach($messages as $item)
                @if($item['style'] == 'groupend')
                    <?php $group-- ?>
                    <span style="font-size:13px;padding:3px 20px 3px 10px;position: relative; left:-2px; color:white;background-color:#F4645F;border-bottom: 1px solid #F4645F">End {{$item['name'] or ''}} ({{$item['time']}}ms)</span>
                    </div>
                @endif
                @if($item['style'] == 'group')
                    <?php $dl=$group*20;  ?>
                    <div style="font-size:13px; margin-left: {{$dl}}px;border-left: 2px solid #F4645F; margin-bottom: 12px">
                        <span style="margin-bottom:10px; padding:3px 20px 3px 10px;background: #F4645F; color:white; position: relative; top:2px">{{$item['name'] or ''}} ({{$item['time']}}ms)</span>
                        <div style="height: 10px">&nbsp;</div>
                        <?php $group++?>
                @endif
                @if(in_array($item['style'], ['table','info','error','log', 'warning', 'success']))
                <?php 
                $panelStyle = $item['style']; 
                if($panelStyle=='error') $panelStyle='danger';
                if($panelStyle=='log') $panelStyle='default'; ?>
                <div style="margin-left:10px; border: 1px solid #ddd; margin-bottom:10px;box-shadow: 0px 0px 2px #ccc;">
                    <div style="padding:5px 10px;border-bottom: 1px solid #ddd ">
                        <strong>{{$item['name'] or ''}} ({{$item['trace']}})</strong>
                        @if(in_array($item['style'], ['info', 'warning', 'error', 'success']))
                            <span class="label label-{{$panelStyle}}" style="position:relative; top:-1px">{{strtoupper($item['style'])}}</span>
                        @endif  
                        @if(is_object($item['value']))
                            <span class="pull-right">
                                <?php
                                    $class = ''; $url='';
                                    if(starts_with(get_class($item['value']), 'Illuminate'))
                                    {    
                                        $class = get_class($item['value']);
                                    } else {
                                        $class = inspector()->getIlluminateAncestor($item['value']); 
                                    }
                                    if($class != '')
                                    {
                                        $url = str_replace("\\", "/",$class).'.html'; 
                                        $ver = substr(app()->version(),0,3);
                                        $url = "https://laravel.com/api/$ver/".$url;
                                    }
                                    ?>
                                    @if($url!='')
                                        <a href="{{$url}}" target="_blank"><strong>{{ get_class($item['value'])}}</strong></a>                            
                                    @else
                                        <strong>{{ get_class($item['value'])}}</strong>
                                    @endif
                            </span>
                        @endif
                    </div>
                    <div class="panel-body">
                        @if(isset($item['value']))
                            @if($item['style']=='table' && is_array($item['value']) && count($item['value'])>0)
                                @include("inspector::fs_table",['data'=>$item['value']])
                            @else
                                {!!inspector()->getDump($item['value'])!!}
                           
                            @endif
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
