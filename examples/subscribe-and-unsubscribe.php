<?php declare(strict_types=1);

use m50\Pusher\Client;
use m50\Pusher\Event;

use function Amp\async;
use function Amp\delay;

include_once('vendor/autoload.php');

$client = Client::create('my-app', 'us3');

$channel = $client->channel('my-channel');
async(function () use ($channel) {
    echo 'in async' . PHP_EOL;
    $channel->subscribe(function (Event $event) use ($channel) {
        var_dump($event);
        if ($event->event === 'END') {
            $channel->unsubscribe();
        }
    });
});

delay(30);
echo 'delayed' . PHP_EOL;
$client->close();
echo 'closed' . PHP_EOL;
