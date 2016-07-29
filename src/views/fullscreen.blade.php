<?php
//dd($collectors);
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, shrink-to-fit=no, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Laravel Inspector</title>

    <style type="text/css">
    	@include("inspector::bootstrap");
        @include("inspector::style");
    </style>

    <style type="text/css">
    	.inspector-panel {
    		display: none;
    	}
        .timeline-app {
            background-color: #FFB6B4;
            border-radius: 3px;
            height: 20px;
            color: #000;
            padding-left: 4PX;
        }
        .timeline-timer {
            background-color: #78C6FF;
            border-radius: 3px;
            height: 20px;
            color: #000;
            padding-left: 4PX;
        }
        .timeline-sql {
            background-color: #F1C40F;
            border-radius: 3px;
            height: 20px;
               color: #000;
            padding-left: 4PX;
        }
        .timestamp {
          width: 0; 
          height: 0; 
          border-left: 10px solid transparent;
          border-right: 10px solid transparent;
          border-bottom: 10px solid #27AE60;
          position: relative;
          left: -10px;
          top: 5px;
        }   
    </style>
    <script>
        @include('inspector::jquery');
    </script>
</head>
<body>
    <div id="wrapper">
        <div id="sidebar-wrapper" style="float: left; width: 220px">
            <ul class="sidebar-nav sidebar-inverse">
                <li class="sidebar-brand">
                    <div style="margin-top: 10px;margin-bottom: 10px;text-align: center; color: white; font-size:22px">
                        Laravel Inspector
                    </div>
                </li>
                @foreach($collectors as $key=>$value)
                    @if($value['count'])
                    <li>
                        <a class="menu-item " href="#" id='panel-{{$key}}-item' data-panel='panel-{{$key}}'>{{strtoupper($key)}}
                        @if($value['showCounter'])
                            <span class="badge badge-info">{{$value['count']}}</span>
                        @endif
                        </a>
                    </li>   
                    @endif
                @endforeach
                <li class="sidebar-stats" style="border-bottom: 0">
                     <div style="margin-top:1px;line-height: 1.8em; font-weight:300;color:white; padding-left: 10px">
                        <div style="height: 23px; font-size: 18px">{{$time}}ms</div>
                        <div style="font-size:12px; height: 34px;">TOTAL REQUEST TIME</div>
                        
                        @if(isset($collectors['SQLs']))
                        <div style="height: 23px; font-size: 18px">{{$collectors['SQLs']['items']['time']}}ms</div>
                        <div style="font-size:12px; height: 34px;">TOTAL DB ACCESS TIME</div>
                        @endif
                        <div style="height: 23px; font-size: 18px">{{$memoryUsage}} / {{ini_get('memory_limit')}}B</div>
                        <div style="font-size:12px; height: 34px;">USED RAM/LIMIT</div>

                        <div style="height: 23px; font-size: 18px">{{app()->version()}} / {{phpversion()}}</div>
                        <div style="font-size:12px; height: 34px;">LARAVEL/PHP VERSIONS</div>

                        <br> 
                    </div>
                </li>
            </ul>
        </div>

        <div id="page-content-wrapper">
            @include('inspector::fs_messages')
            @include('inspector::fs_exceptions')
            @include('inspector::fs_sqls')
            @include('inspector::fs_server')
            @include('inspector::fs_session')
            @include('inspector::fs_request')
            @include('inspector::fs_routes')
            @include('inspector::fs_timers')
        </div>
    </div>
    <script>
    @foreach($collectors as $key=>$value)
        @if($value['count']>0)
                var activePanel = '#panel-{{$key}}';
            @break
        @endif
    @endforeach
        $(activePanel).show();
    $(activePanel+'-item').addClass('active');

    $(activePanel+'-item').addClass('active');
        $(".menu-item").click(function(e) {
        e.preventDefault();
        var panel = '#'+$(this).attr('data-panel');
        $(activePanel).fadeOut(0);
        $(activePanel+'-item').removeClass('active');
        $(panel).fadeIn(200);
        $(this).addClass( "active" );
        activePanel = panel;
    });
    </script>
</body>