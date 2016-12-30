<?php

// Just start this server and connect with any number of clients to it.
// Everything a client sends will be broadcasted to all connected clients.
//
// $ php examples/02-chat-server.php 8000
// $ telnet localhost 8000

use React\EventLoop\Factory;
use React\Socket\Server;
use React\Socket\ConnectionInterface;

require __DIR__ . '/../vendor/autoload.php';

$loop = Factory::create();

$server = new Server($loop);
$server->listen(isset($argv[1]) ? $argv[1] : 0, '0.0.0.0');

$clients = array();

$server->on('connection', function (ConnectionInterface $client) use (&$clients) {
    // keep a list of all connected clients
    $clients []= $client;
    $client->on('close', function() use ($client, &$clients) {
        unset($clients[array_search($client, $clients)]);
    });

    // whenever a new message comes in
    $client->on('data', function ($data) use ($client, &$clients) {
        // remove any non-word characters (just for the demo)
        $data = trim(preg_replace('/[^\w\d \.\,\-\!\?]/u', '', $data));

        // ignore empty messages
        if ($data === '') {
            return;
        }

        // prefix with client IP and broadcast to all connected clients
        $data = $client->getRemoteAddress() . ': ' . $data . PHP_EOL;
        foreach ($clients as $client) {
            $client->write($data);
        }
    });
});

echo 'Listening on ' . $server->getPort() . PHP_EOL;

$loop->run();
