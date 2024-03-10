<?php declare(strict_types=1);

use m50\Pusher\Client;
use m50\Pusher\Event;

include_once('vendor/autoload.php');

$client = Client::create('my-app', 'us3');

$channel = $client->channel('my-channel');
$channel->subscribe(function (Event $event) use ($channel) {
    var_dump($event);
    if ($event->event === 'END') {
        $channel->unsubscribe();
    }
});
