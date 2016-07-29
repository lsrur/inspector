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
    </style>
</head>

<body>

    <div id="wrapper">
        <!-- Sidebar -->
            <div id="sidebar-wrapper" style="float: left; width: 220px">
                <ul class="sidebar-nav sidebar-inverse">
                    <li class="sidebar-brand">
                        <div style="text-align: center; margin-bottom:20px;color: white; font-size:22px">Laravel Inspector</div>
                       
                    </li>
                    @if(isset($exception))
                    <li>
                        <a class="menu-item active" href="#" id='panel-exception-item' data-panel='panel-exception'>EXCEPTION
                        <span class="badge badge-info">1</span>
                        </a>
                    </li>    
                    @endif              
                    @if(isset($messageCount) && $messageCount>0)
                    <li>
                        <a class="menu-item" href="#" id='panel-debug-item' data-panel='panel-debug'>
                        MESSAGES<span class="badge badge-info">{{$messageCount}}</span></a>
                    </li>
                    @endif
                    @if($sql['count'])
                    <li>
                        <a class="menu-item" id='panel-sqls-item' data-panel='panel-sqls' href="#">SQLs
                        <span class="badge badge-info">{{$sql['count']}}</span>
                        </a>
                    </li>
                    @endif
                    <li>
                        <a class="menu-item" id='panel-request-item' data-panel='panel-request' href="#">REQUEST</a>
                    </li>
                    <li>
                        <a class="menu-item" id='panel-server-item' data-panel='panel-server' href="#">SERVER</a>
                    </li>
                    <li>
                        <a class="menu-item" id='panel-session-item' data-panel='panel-session' href="#">SESSION</a>
                    </li>
                    <li>
                        <a class="menu-item" id='panel-phpinfo-item' data-panel='panel-phpinfo' href="#">ROUTES</a>
                    </li>
                    <li>
                        <a class="menu-item" id='panel-phpinfo-item' data-panel='panel-phpinfo' href="#">LOGs</a>
                    </li>
                    
                    <li>
                        <a class="menu-item" id='panel-phpinfo-item' data-panel='panel-phpinfo' href="#">PHP INFO</a>
                    </li>
                    <li class="sidebar-stats" style="border-bottom: 0">
                         <div style="margin-top:14px;line-height: 1.8em; font-weight:300;color:white; padding-left: 10px">
                            <div style="height: 24px; font-size: 22px">{{$time}}ms</div>
                            <div style="font-size:12px; height: 28px;">TOTAL REQUEST TIME</div>
                            <br>
                            @if(isset($sql['time']))
                            <div style="height: 24px; font-size: 22px">{{$sql['time']}}ms</div>
                            <div style="font-size:12px; height: 28px;;">DB ACCESS TIME</div>
                            <br>
                            @endif
                            <div style="height: 24px; font-size: 22px">{{$allocRam}}</div>
                            <div style="font-size:12px; height: 28px;">ALLOCATED MEMORY</div>
                            <br>
                            <div style="height: 24px; font-size: 22px">{{ini_get('memory_limit')}}B</div>
                            <div style="font-size:12px; height: 28px;">PHP MEMORY LIMIT</div>
                            <div style="height: 24px; font-size: 22px">LARAVEL/PHP</div>
                            <div style="font-size:12px; height: 28px;;">VERSIONS</div>

                            <br> 
                        </div>
                    </li>
                </ul>
            </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            @if(isset($messages) && count($messages)>0)
            @include('inspector::fs_debug')
            @endif

         	@include('inspector::fs_sqls')
            @if(isset($exception))
         	@include('inspector::fs_exception')
            @endif

            @include('inspector::fs_request')

            @include('inspector::fs_server')

            @include('inspector::fs_session')
          


            @include('inspector::fs_phpinfo')



        </div>
        <!-- /#page-content-wrapper -->

    </div>
    <!-- /#wrapper -->

    <!-- jQuery -->
    <script>
    	@include('inspector::jquery');
    </script>

    <script>
    @if(isset($exception))
    var activePanel = '#panel-exception';
    @elseif(isset($messages) && count($messages)>0)
    var activePanel = '#panel-debug';
    @elseif($sql['count'])>0)
    var activePanel = '#panel-sql';
    @else
    var activePanel = '#panel-request';
    @endif;
    
    $(activePanel).show();
    $(activePanel+'-item').addClass('active');

    $(".menu-item").click(function(e) {
        e.preventDefault();
        console.log(e);
    	var panel = '#'+$(this).attr('data-panel');

        $(activePanel).fadeOut(0);
    	$(activePanel+'-item').removeClass('active');
    	$(panel).fadeIn(200);
        $(this).addClass( "active" );
    	activePanel = panel;
        

    	console.log('click', activePanel);
    });
    </script>

</body>

</html>

<?php
    // } catch (Exception $e) {
    //     dump($e);
    //     die();
    // }
?>
