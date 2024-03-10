<?php declare(strict_types=1);

use m50\Pusher\Client;

include_once('vendor/autoload.php');

$client = Client::create('my-app', 'us3');
$channel = $client->channel('my-channel');

while ($event = $channel->next(300)) {
    dump($event);
}
