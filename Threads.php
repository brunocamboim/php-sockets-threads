<?php

require_once 'Sockets.php';

class Threads extends Thread {

    public function __construct($arg) {
        $this->arg = $arg;
    }

    public function run() {
        if ($this->arg) {

            switch($this->arg) {

                case 'SOCKET':

                    $socket = new Sockets();
                    
                    $socket->createSocket();

                    break;

                case 'CONT':

                    $sleep = mt_rand(1, 10);
                    printf('%s: %s  -start -sleeps %d' . "\n", date("g:i:sa"), $this->arg, $sleep);
                    sleep($sleep);
                    printf('%s: %s  -finish' . "\n", date("g:i:sa"), $this->arg);
                    
                    break;

                case 'IPS':

                    #pegar ips conhecidos
                    $ips = [];
                    if (file_exists("./ips.txt")) {
                        $ips = file("./ips.txt", FILE_IGNORE_NEW_LINES);
                    }

                    break;

            }   
        }
    }
    
}