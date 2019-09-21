<?php

error_reporting(~E_WARNING);

$server = getHostByName(getHostName());
$port = 9999;

if (!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))) {
	$errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    
    die("Couldn't create socket: [$errorcode] $errormsg \n");
}

echo "Socket created \n";

while(1) {
	
	echo 'Enter a message to send : ';
	$input = fgets(STDIN);
	
	if (!socket_sendto($sock, $input , strlen($input) , 0 , $server , $port)) {
		$errorcode = socket_last_error();
		$errormsg = socket_strerror($errorcode);
		
		die("Could not send data: [$errorcode] $errormsg \n");
	}
		
	if (socket_recv($sock, $reply, 2045, MSG_PEEK) === FALSE){
		$errorcode = socket_last_error();
		$errormsg = socket_strerror($errorcode);
		
		die("Could not receive data: [$errorcode] $errormsg \n");
	}
	
	echo "Reply : $reply";
}