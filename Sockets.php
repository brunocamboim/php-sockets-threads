<?php

require_once 'Helper.php';

class Sockets {
    
    private $port;
    private $address;

    function __construct() {

        $this->port = 29000;
        $this->address = getHostByName(getHostName());

    }

    public function getPort(){
        return $this->port;
    }

    public function getAddress(){
        return $this->address;
    }

    public function setPort($value){
        $this->port = $value;
    }

    public function setAddress($value){
        $this->address = $value;
    }

    public function createSocket() {

        if (!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            
            die("Erro ao criar o socket: [$errorcode] $errormsg \n");
        }
        
        echo "Socket criado \n";
        
        if (!socket_bind($sock, $this->address, $this->port)) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            
            die("Erro ao fazer o bind do ip e porta: [$errorcode] $errormsg \n");
        }

        do {

            clearstatcache();
        
            $r = socket_recvfrom($sock, $buf, 2045, 0, $remote_ip, $remote_port);
            echo "$remote_ip : $remote_port -- $buf \n" ;

            $buffer = Helper::removeLineBreaks(explode(";", $buf));

            $return = null;

            switch (strtoupper($buffer[0])) {
                #pedir todos arquivos - codigo e nome dos arquivos
                case 'PTA':

                    $return .= "ETA;";
                    foreach (new DirectoryIterator('./my_files') as $fileInfo) {
                        if($fileInfo->isDot()) continue;

                        $return .= $fileInfo->getFilename() . ",";
                    }

                    $return = rtrim($return, ",");

                    break;

                #pedir arquivo especifico - Codigo, tamanho, nome e dados
                case 'PAE':

                    if (isset($buffer[1])) {
                        $nome       = $buffer[1];
                        $dir_file   = './my_files/'.$buffer[1];

                        if (file_exists($dir_file)) {
                            $return .= "EAE;";

                            $file = file_get_contents($dir_file);
                            $return .= filesize($dir_file) . ";$nome;$file";
                        }
                    }

                    break;

            }

            if (empty($return)) {
                $return = "COMANDO NAO RECONHECIDO";
            }

            if( !socket_sendto($sock, $return, strlen($return) , 0 , $remote_ip , $remote_port) )
            {
                $errorcode = socket_last_error();
                $errormsg = socket_strerror($errorcode);
                
                echo "Erro ao mandar de volta\n";
            }
        
        } while (true);
        
        socket_close($sock);

    }

}

?>