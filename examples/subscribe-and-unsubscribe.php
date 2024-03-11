<?php declare(strict_types=1);

use m50\Pusher\Client;
use m50\Pusher\Event;

use function Amp\coroutine;

include_once('vendor/autoload.php');

Amp\Loop::run(coroutine(function () {
    $client = yield Client::create('my-app', 'us3');

    $channel = yield $client->channel('my-channel');
    yield $channel->subscribe(function (Event $event) use ($channel) {
        var_dump($event);
        if ($event->event === 'END') {
            $channel->unsubscribe();
        }
    });
}));
