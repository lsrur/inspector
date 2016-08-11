<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, shrink-to-fit=no, initial-scale=1">
    <title>Laravel Inspector</title>
    @include("inspector::fs_vendor")
    <style type="text/css">
    pre {
        border-radius: 0;
        border: 0px;
        padding: 0px 10px !important;
        margin:0px !important;
    }

    body {
        overflow-x: hidden;
    }

    #wrapper {
        padding-left: 0;
        -webkit-transition: all 0.5s ease;
        -moz-transition: all 0.5s ease;
        -o-transition: all 0.5s ease;
        transition: all 0.5s ease;
    }

    #wrapper.toggled {
        padding-left: 220px;
    }

    #sidebar-wrapper {
        z-index: 1000;
        position: fixed;
        left: 220px;
        width: 0;
        height: 100%;
        margin-left: -220px;
        overflow-y: auto;
        overflow-x: hidden;
        background: #F4645F;
        color: #fff,
        -webkit-transition: all 0.5s ease;
        -moz-transition: all 0.5s ease;
        -o-transition: all 0.5s ease;
        transition: all 0.5s ease;
    }

    #wrapper.toggled #sidebar-wrapper {
        width: 220px;
    }

    #page-content-wrapper {
        width: 100%;
        position: absolute;
        padding: 15px;
    }

    #wrapper.toggled #page-content-wrapper {
        position: absolute;
        margin-right: -220px;
    }

    /* Sidebar Styles */

    .sidebar-nav {
        position: absolute;
        top: 0;
        width: 220px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .sidebar-nav li {

        line-height: 40px;
        border-bottom:1px solid #F4A97B;
    }

    .sidebar-nav li:first-child a {
        border-top:1px solid #F4A97B;

    }
    .sidebar-nav li a {
        display: block;

        text-decoration: none;
        color: #FFF;
        padding-left:10px;
    }

    .sidebar-nav li a.active:before {
        float:right;
        position:relative;
        font-size:18px;
        left: 6px;
        top:0px;
        content: "\25C0"; 
    }

    .sidebar-nav li a:hover {
        text-decoration: none;
        color: #fff;
        background: rgba(255,255,255,0.2);
    }

    .badge-info {
        background-color: #FFDFD8;
        color: #F4645F;    
        position: relative;
        top: -2px;
        left: 7px;
        font-size: 11px;
    }

    .sidebar-nav li a:active,
    .sidebar-nav li a:focus {
        text-decoration: none;
    }

    .sidebar-nav > .sidebar-stats {
        padding: 20px 0px;
    }

    .sidebar-nav > .sidebar-brand a {
        color: #FFF;
    }

    .sidebar-nav > .sidebar-brand a:hover {
        color: #fff;
        background: none;
    }
    .modal-backdrop {
        z-index: 1001;
    }
    .table-modal {
        position: fixed; 
        top: 10%; 
        bottom: 10%; 
        left: 10%; 
        right: 10%; 
        border : 1px solid #ddd;
        background: #fff; 
        box-shadow: 2px 2px 5px #aaa;
        z-index: 9999999;
        display: none;
    }

    @media(min-width:768px) {
        #wrapper {
            padding-left: 220px;
        }

        #wrapper.toggled {
            padding-left: 0;
        }

        #sidebar-wrapper {
            width: 220px;
        }

        #wrapper.toggled #sidebar-wrapper {
            width: 0;
        }

        #page-content-wrapper {
            padding: 20px;
            position: relative;
        }

        #wrapper.toggled #page-content-wrapper {
            position: relative;
            margin-right: 0;
        }


        table.debug-table {
            padding: 0;
            margin: 0;
            font-family: "Courier New", Courier, "Lucida Sans Typewriter", "Lucida Typewriter", monospace;
            font-size: 14px;
        }

        td.debug-key-cell {
            vertical-align: top;
            padding: 3px;
            border: 1px solid #AAAAAA;
        }

        td.debug-value-cell {
            vertical-align: top;
            padding: 3px;
            border: 1px solid #AAAAAA;
        }

        div.debug-item {
            border-bottom: 1px dotted #AAAAAA;
        }

        span.debug-label {
            font-weight: bold;
        }
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

        a {
            text-decoration: none !important;

        }
        a:focus {
            outline: 0px;        }
    </style>

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
                @if($analizeView)
                <li>
                    <a class="menu-item " href="#" id='panel-view-item' data-panel='panel-view'>CONTENT</a>
                </li>
                @endif
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
                <li>
                    <a class="menu-item " href="#" id='panel-phpinfo-item' data-panel='panel-phpinfo'>PHP INFO</a>
                </i>
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
                        <div style="height: 23px; font-size: 18px;overflow: hidden;">{{app()->version()}} / {{phpversion()}}</div>
                        <div style="font-size:12px; height: 34px;">LARAVEL/PHP VERSIONS</div>
                        <br> 
                    </div>
                </li>
            </ul>
        </div>
        <div id="page-content-wrapper">
            @if($analizeView)
            @include('inspector::fs_view')  
            @endif
            @include('inspector::fs_messages')
            @include('inspector::fs_exceptions')
            @include('inspector::fs_sqls')
            @include('inspector::fs_server')
            @include('inspector::fs_session')
            @include('inspector::fs_request')
            @include('inspector::fs_routes')
            @include('inspector::fs_timers')
            @include('inspector::fs_phpinfo')
            @include('inspector::fs_response')

        </div>
    </div>
    <div class="modal fade" id="table-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 id="table-modal-title" id="myModalLabel"></h4>
                </div>
                <div class="modal-body">
                    <pre id="table-modal-pre"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        @if($analizeView)
            var activePanel = '#panel-view';
        @else
            @foreach($collectors as $key=>$value)
                @if($value['count']>0)
                    var activePanel = '#panel-{{$key}}';
                    @break
                @endif
            @endforeach
        @endif
        $(activePanel).show();
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

        $(document).on("click", ".table-array-link", function () {
            $('#table-modal-title').html($(this).data('title'));
            $('#table-modal-pre').html($(this).data('code'));
        });

    </script>
</body>