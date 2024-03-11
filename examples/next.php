<?php declare(strict_types=1);

use m50\Pusher\Client;

use function Amp\coroutine;

include_once('vendor/autoload.php');

Amp\Loop::run(coroutine(function () {
    /** @var Client $client */
    $client = yield Client::create('my-app', 'us3');
    /** @var Channel $channel */
    $channel = yield $client->channel('my-channel');

    try {
        /** @var Event $event */
        while ($event = yield $channel->next(300)) {
            dump($event);
        }
    } catch (\Throwable $e) {
        dump($e->getMessage());
    }
}));
