<?php

declare(strict_types=1);

namespace m50\Pusher;

use Amp\Websocket\Client\WebsocketConnection;
use Traversable;

use function Amp\Websocket\Client\connect;

class Client implements \IteratorAggregate
{
    private array $channels = [];

    public function __construct(
        private WebsocketConnection $conn
    ) {
        register_shutdown_function(function () {
            $this->close();
        });
    }

    public function getIterator(): Traversable
    {
        foreach ($this->conn as $message) {
            yield Event::fromWebsocketMessage($message);
        }
    }

    public static function create(string $appId, ?string $cluster = null): Client
    {
        $connection = connect(ApiSettings::createUrl($appId, $cluster));

        return new Client($connection);
    }

    public function channel(string $channel): Channel
    {
        if (isset($this->channels[$channel])) {
            return $this->channels[$channel];
        }

        $this->send(Event::subscribeTo($channel));

        return $this->channels[$channel] = new Channel($channel, $this->conn, function () use ($channel) {
            $this->send(Event::unsubscribeFrom($channel));
            unset($this->channels[$channel]);
        });
    }

    public function send(Event|array $message): void
    {
        $this->conn->sendText(\json_encode($message));
    }

    public function close(): void
    {
        foreach ($this->channels as $channel) {
            $channel->close();
        }
        $this->conn->close();
    }
}
