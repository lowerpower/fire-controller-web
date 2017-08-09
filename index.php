<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<link href="/css/uploadfile.css" rel="stylesheet">

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
    
    //if (lastClicked) lastClicked.className='';
    //lastClicked = el;
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

window.onload= function() {
    document.body.appendChild(grid);
}

</script>
</head>


<body>

</body>

hello

</html>

