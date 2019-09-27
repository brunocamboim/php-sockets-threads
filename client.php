<?php

require_once 'Helper.php';

error_reporting(~E_WARNING);

$server = getHostByName(getHostName());
$port = 6000;

if (!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))) {
	$errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    
    die("Couldn't create socket: [$errorcode] $errormsg \n");
}

echo "Socket created \n";

# array para requisitar a busca de arquivos especificos
$send_request_files = array();

while(1) {

    if (!empty($send_request_files)) {

        $send_request_files = array();
    }

	
	echo 'Enter a message to send : ';
	$input = fgets(STDIN);
	
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

    $reply = Helper::removeLineBreaks(explode(",", $reply));

    switch (strtoupper($reply[0])) {
        #pedir todos arquivos - codigo e nome dos arquivos
        case 'ETA':

            $my_files = array();
            foreach (new DirectoryIterator('./files') as $fileInfo) {
                if($fileInfo->isDot()) continue;
                $my_files[] = $fileInfo->getFilename();
            }

            for ($i = 1; $i < sizeof($reply); $i++) {
                if (!in_array($reply[$i], $my_files)) {
                    #pede o arquivo pra salvar
                    $send_request_files[] = $reply[$i];
                }
            }

            break;

        #pedir arquivo especifico - Codigo, tamanho, nome e dados
        case 'EAE':

            $tamanho = $reply[1];
            $nome = $reply[2];
            $dados = $reply[3];

            $dir_file   = './files/'.$nome;

            # verifica se eu tenho o arquivo, se não, crio em minha base
            if (file_exists($dir_file)) {
                if (filesize($dir_file) != $tamanho) {

                }
            } else {
                $file = fopen($dir_file, "w");
                fwrite($file, $dados);
                fclose($file);
            }

            break;

    }
	
	echo "Reply : $reply[0] \n";

	unset($reply);
}