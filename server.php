<?php

require_once 'Threads.php';

error_reporting(E_ALL);

set_time_limit(0);

ob_implicit_flush();

/**
 * Primeiro parametro: Cria o socket
 * Segundo parametro: Cria o contador pra testar a thread
 * Terceiro parametro: Carrega os ips conhecidos e busca os arquivos comparando com os meus
 */
$tasks = array("SOCKET_SERVER", "CONT", "IPS");

# instancia as threads
foreach ( $tasks as $i ) {
    $stack[] = new Threads($i);
}

# inicia as threads
foreach ( $stack as $t ) {
    $t->start();
}

exit;