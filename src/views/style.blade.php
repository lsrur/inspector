/*!
 * Start Bootstrap - Simple Sidebar (http://startbootstrap.com/)
 * Copyright 2013-2016 Start Bootstrap
 * Licensed under MIT (https://github.com/BlackrockDigital/startbootstrap/blob/gh-pages/LICENSE)
 */



pre {
   border-radius: 0;
}

 body {
    overflow-x: hidden;
 }

/* Toggle Styles */

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