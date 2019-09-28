<?php

require_once 'Helper.php';

error_reporting(~E_WARNING);

$server = getHostByName(getHostName());
$port = 29000;

if (!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))) {
	$errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    
    die("Couldn't create socket: [$errorcode] $errormsg \n");
}

echo "Socket created \n";

# array para requisitar a busca de arquivos especificos
$send_request_files = array();

while(1) {

    clearstatcache();

    $input = "PTA";

    if (!empty($send_request_files)) {

        $input = "PAE;" . $send_request_files[0];
        unset($send_request_files[0]);

        $send_request_files = array_values($send_request_files);
    }
	
	if (!socket_sendto($sock, $input , strlen($input) , 0 , $server , $port)) {
		$errorcode = socket_last_error();
		$errormsg = socket_strerror($errorcode);
		
		die("Could not send data: [$errorcode] $errormsg \n");
	}
		
	if (socket_recv($sock, $reply, 2045, 0) === FALSE){
		$errorcode = socket_last_error();
		$errormsg = socket_strerror($errorcode);
		
		die("Could not receive data: [$errorcode] $errormsg \n");
	}

    $reply = explode(";", $reply);

    switch (strtoupper($reply[0])) {
        #pedir todos arquivos - codigo;nome_arquivo1,nome_arquivo2
        case 'ETA':

            if (!empty($reply[1])) {
                $dados = explode(",", $reply[1]);
                $send_request_files = $dados;
            }

            break;

        #pedir arquivo especifico - Codigo;tamanho;nome;dados
        case 'EAE':

            $tamanho = $reply[1];
            $nome = $reply[2];
            $texto = $reply[3];

            if (sizeof($reply) > 4) {
                for ($i = 4; $i < sizeof($reply); $i++) {
                    $texto .= ";" . $reply[$i];
                }
            }

            var_dump($tamanho, $nome, $texto);

            $dir_file   = './files/'.$nome;

            # verifica se eu tenho o arquivo e tem o tamanho diferente ou se ele nao existe eu crio em minha base
            if ((file_exists($dir_file) && filesize($dir_file) != $tamanho) || !file_exists($dir_file)) {
                $file = fopen($dir_file, "w");
                fwrite($file, $texto);
                fclose($file);
            }

            break;

    }
	
	echo "Reply : $reply[0] \n";

	unset($reply);

	sleep(3);
}