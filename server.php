<?php

require_once 'Threads.php';

error_reporting(E_ALL);

set_time_limit(0);

ob_implicit_flush();

/**
 * Primeiro parametro: Cria o socket
 * Demais parametros: Ip dos servidores conhecidos
 */
$tasks = array("SOCKET_SERVER");

#pegar ips conhecidos
if (file_exists("./ips.txt")) {
    $ips = file("./ips.txt", FILE_IGNORE_NEW_LINES);
    foreach ($ips as $value) {
        $tasks[] = $value;
    }
}

# instancia as threads
foreach ( $tasks as $i ) {
    $stack[] = new Threads($i);
}

# inicia as threads
foreach ( $stack as $t ) {
    $t->start();
}

exit;