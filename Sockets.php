<?php

class Sockets {
    
    private $port;
    private $address;

    function __construct() {

        $this->port = 9999;
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
            
            die("Couldn't create socket: [$errorcode] $errormsg \n");
        }
        
        echo "Socket criado \n";
        
        if (!socket_bind($sock, $this->address, $this->port)) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            
            die("Could not bind socket : [$errorcode] $errormsg \n");
        }

        do {
	
            echo "Esperando conexão\n";
        
            $r = socket_recvfrom($sock, $buf, 512, 0, $remote_ip, $remote_port);
            echo "$remote_ip : $remote_port -- " . $buf;
        
            if( !socket_sendto($sock, "OK " . $buf , 100 , 0 , $remote_ip , $remote_port) )
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