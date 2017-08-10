<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<link href="/css/uploadfile.css" rel="stylesheet">
<link href="/cssChanges.css" rel="stylesheet">

<!-- <script src="/js/jquery.min.js"></script> -->

<script src="/js/jquery-1.12.4.js"></script>
<script src="/js/dynamic-table.js"></script>

<script language="javascript" type="text/javascript">

//    const WebSocket = require('ws');
    
    var loc = window.location, new_uri;
    if (loc.protocol === "https:") {
        new_uri = "wss:";
    } else {
        new_uri = "ws:";
    }
    new_uri += "//" + loc.host + ":8080";
    new_uri += loc.pathname ;

    function next() {
      ws.send('set '+Math.floor((Math.random() * 0xffffffff) + 1));
    }

    var ws = new WebSocket(new_uri);

    console.log("websock connect to "+new_uri);

    ws.onclose = function() {
        console.log('DISCONNECT');
    };

    ws.onopen = function() {
        console.log('CONNECTED');
        ws.send('set 0');
    };

    ws.onmessage = function(data) {
      console.log('message');
      console.log(data.data);
      //setTimeout(next,500);

    };
/*
    socket.addEventListener('message', function (event) {
        console.log('Message from server ', event.data);
    });
*/

// padd zero (number, width, z set if not zero pad)
function pad(n, width, z) {
  z = z || '0';
  n = n + '';
  return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}


var lastClicked;
var currentState1=0;
var currentState2=0;

var grid = clickableGrid(6,8,function(el,row,col,i){
    console.log("You clicked on element:",el);
    console.log("You clicked on row:",row);
    console.log("You clicked on col:",col);
    console.log("You clicked on item #:",i);
    console.log("item class name:",el.className);

    send_val=send_val2=0;
    if('clicked'==el.className)
    {
        el.className='';
        ws.send('set 0');
    }
    else
    {
        el.className='clicked';
        if(i>32)
        {
            send_val2=1<<(i-33);
        }
        else
        {
            send_val=Math.abs(1<<(i-1));
        }
        send_val_string= pad(send_val.toString(16),8);
        send_val_string2= pad(send_val2.toString(16),8);
        
        console.log("setting bitmask:",send_val_string);
        console.log("setting bitmask2:",send_val_string2);
        console.log("setting command: set ",send_val_string2+send_val_string);

        
        ws.send('set '+send_val_string2+send_val_string);
    }
    
    if ((lastClicked)&&(lastClicked!=el)) lastClicked.className='';
    lastClicked = el;
});

     
function clickableGrid( rows, cols, callback ){
    var i=0;
    var grid = document.createElement('table');
    grid.className = 'grid';
    for (var r=0;r<rows;++r){
        var tr = grid.appendChild(document.createElement('tr'));
        for (var c=0;c<cols;++c){
            var cell = tr.appendChild(document.createElement('td'));
            cell.innerHTML = ++i;
            cell.addEventListener('click',(function(el,r,c,i){
                return function(){
                    callback(el,r,c,i);
                }
            })(cell,r,c,i),false);
        }
    }
    return grid;
}

function click_dir(item)
{
    console.log("clickdir"+item);
}

function load_dir() 
{
    var i=0;
    var the_api_url = "/dir.php";

    var table = document.getElementById('data-table');
    $("#data-table").empty();
    table.className = 'dir';

    $.getJSON(the_api_url, {}, function(data) {
        $.each(data, function(idx, item){
            
            var tr   = table.appendChild(document.createElement('tr'));
            var cell = tr.appendChild(document.createElement('td'));
            console.log("item:",item);

            cell.innerHTML=item;
            cell.addEventListener('click',(function(item){
                return function(){
                    click_dir(item);
                }
            })(item),false);
            
            //console.log("data:",item);
        });
    });
}

/*
function load_dir(dt)
{
    $.get("/dir.php", function(data, status){
        console.log("data:",data);
        dt.load(data);
    });
}
*/

window.onload= function() {

    var theDiv = document.getElementById("leftContent");
    theDiv.appendChild(grid);
    load_dir();
/*
    $.get("/dir.php", function(data, status){
                alert("Data: " + data + "\nStatus: " + status);
    });

    $.getJSON("dir.php", function(result){
        $.each(result, function(i, field){
            $("div").append(field + " ");
    });
*/


//document.body.container1.leftContent.appendChild(grid);
}

</script>
</head>


<body>

<div id="main-container">

  <div id="leftContent">
    <div><h4 align="center">Relay Status - Single Clickable</h4>
    </div>
  </div>
  
  <div id="mainContent">

    <h4 align="center">Riff Files - Click to Queue</h4>
    
    <table id="data-table"></table>

    <br />
    <button id="btn-load">Reload Dir</button>&nbsp;
    <button id="btn-clear">Clear List</button>&nbsp;
  
  </div> 
  
  <div id="rightContent">
  
    <h4 align="center">Currently Playing</h4>
    
    <table id="play-table"></table>

    <br />
    <button id="btn-play">Play</button>&nbsp;
    <button id="btn-pause">Pause</button>&nbsp;
    
  </div>

</div>

</body>


</html>

