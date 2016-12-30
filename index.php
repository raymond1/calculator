<?php

if ($_REQUEST['mode']=='get_script'){
	produce_javascript();
	exit;
}else if ($_REQUEST['mode'] == 'get_time'){
	produce_time();
	exit;
}
else{
	produce_main_page();
	exit;
}

function produce_javascript(){?>

var i = 0

function timedCount() {
    i = i + 1
    postMessage(i)
    setTimeout("timedCount()",500)
}

timedCount()

<?php
}


function produce_main_page(){?>

<!doctype html>
<html lang='en'>
<head>
<title>Communicator</title>
</head>
<body>
Requires web workers and event source updates.
<div id="display" style='border: thin red solid;'></div>
<div id="display2" style='border: thin yellow solid;'></div>
<div id="display3" style='border: thin green solid;'></div>
<div id="display4" style='border: thin blue solid;'>
	<ul>
	</ul>
</div>
<textarea id="text"></textarea>
<button type="button" id="send">Send</button>

<script src="jquery-3.1.1.min.js"></script>

<script>
	var worker = new Worker("index.php?mode=get_script")
	worker.onmessage = function(event){
		$('#display').text(event.data)
	}


	var source = new EventSource("index.php?mode=get_time")
	source.onmessage=function(event){
		$('#display2').text(event.data)
	}

	var connection = new WebSocket('ws://localhost:8080')
	connection.onopen = function(e){
		console.log("Connection established!")
	}

	connection.onmessage = function(e){
		var message = e.data
		var space_index = message.indexOf(" ")
		var command = message.substring(0, space_index )
		var data = message.substr(space_index + 1)
		if (command == 1){
			//Display the message in display3
			$('#display3').text(data)
		}else if (command == 2){
			$('#display4 ul').append('<li>' + data)
		}
	}

	$('#send').click(function(){
		connection.send($('#text').val())
	})
</script>
</body>
</html>
<?php
}

function produce_time(){
	header('Content-Type: text/event-stream');
	header('Cache-Control: no-cache');

	$time = date('r');
	echo "data: The server time is: {$time}\n\n";
	flush();
}
