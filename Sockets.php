<?php

require_once 'Helper.php';

class Sockets {
    
    private $port;
    private $address;

    function __construct($adress = null) {

        $this->port = 29000;
        $this->address = isset($adress) ? $adress : getHostByName(getHostName());

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
        
        echo " Socket server criado! Meu server: $this->address - $this->port \n";

        if (!socket_bind($sock, $this->address, $this->port)) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            
            die("Erro ao fazer o bind do ip e porta: [$errorcode] $errormsg \n");
        }

        do {

            clearstatcache();

            $r = socket_recvfrom($sock, $buf, 2045, 0, $remote_ip, $remote_port);

            echo "Server recebeu requisicao de: $remote_ip : $remote_port -- $buf \n" ;

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
                
                echo "Erro ao mandar de volta para o cliente!\n";
            }
        
        } while (true);
        
        socket_close($sock);

    }

    public function createSocketClient() {

        sleep(3);

        $server = $this->address;
        $port = $this->port;

        if (!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            die("Nao foi possivel criar o socket do cliente ($this->address) : [$errorcode] $errormsg \n");
        }

        echo "Socket do cliente $this->address criado! \n";

        # array para requisitar a busca de arquivos especificos
        $send_request_files = array();

        while(1) {

            sleep(3);

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

                die("Erro ao enviar dados cliente ($this->address): [$errorcode] $errormsg \n");
            }

            if (socket_recv($sock, $reply, 1000000, 0) === FALSE){
                $errorcode = socket_last_error();
                $errormsg = socket_strerror($errorcode);

                die("Erro ao receber resposta do server ($this->address): [$errorcode] $errormsg \n");
            }

            $reply = explode(";", $reply);

            switch (strtoupper($reply[0])) {
                #pedir todos arquivos - codigo;nome_arquivo1,nome_arquivo2
                case 'ETA':

                    # pega os arquivos que eu tenho dele atualmente
                    $client_files = [];
                    foreach (new DirectoryIterator('./files') as $fileInfo) {
                        if($fileInfo->isDot()) continue;
                        if(strpos($fileInfo->getFilename(), $this->address) === false) continue;
                        $client_files[] = $fileInfo->getFilename();
                    }

                    if (!empty($reply[1])) {
                        $delete_files = [];

                        $dados = explode(",", $reply[1]);
                        $dados_compare = array_map(function($value) use ($server) {
                            return $server . "_" . $value;
                        }, $dados);

                        #verificar os meus arquivos com os enviados pelo server
                        foreach ($client_files as $nome) {
                            if (!in_array($nome, $dados_compare)) {
                                $delete_files[] = $nome;
                            }
                        }

                        # deletar arquivos que estao em minha base mas nao do server
                        if (!empty($delete_files)) {
                            foreach ($delete_files as $nome) {
                                unlink("./files/$nome");
                            }
                        }

                        $send_request_files = $dados;
                    }

                    break;

                #pedir arquivo especifico - Codigo;tamanho;nome;dados
                case 'EAE':

                    $tamanho = $reply[1];
                    $nome = $server . "_" . $reply[2];
                    $texto = $reply[3];

                    # caso o conteudo do arquivo contenha ";"
                    if (sizeof($reply) > 4) {
                        for ($i = 4; $i < sizeof($reply); $i++) {
                            $texto .= ";" . $reply[$i];
                        }
                    }

                    $dir_file   = './files/'.$nome;

                    # verifica se eu tenho o arquivo e tem o tamanho diferente ou se ele nao existe eu crio em minha base
                    if ((file_exists($dir_file) && filesize($dir_file) != $tamanho) || !file_exists($dir_file)) {
                        $file = fopen($dir_file, "w");
                        fwrite($file, $texto);
                        fclose($file);
                    }

                    break;

            }

            echo "Cliente recebeu resposta ($this->address): $reply[0] \n\n";

            unset($reply);

        }
    }

}

?>