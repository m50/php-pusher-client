<?php

declare(strict_types=1);

namespace m50\Pusher;

use Amp\Promise;
use Amp\Websocket\Client\Connection as WebsocketConnection;
use Traversable;

use function Amp\coroutine;
use function Amp\Websocket\Client\connect;

class Client
{
    private array $channels = [];

    public function __construct(
        private WebsocketConnection $conn
    ) {
    }

    public static function create(string $appId, ?string $cluster = null): Promise
    {
        return coroutine(function () use ($appId, $cluster) {
            $connection = yield connect(ApiSettings::createUrl($appId, $cluster));

            return new Client($connection);
        })();
    }

    /** @return Promise<Channel> */
    public function channel(string $channel): Promise
    {
        return coroutine(function () use ($channel) {
            if (isset($this->channels[$channel])) {
                return $this->channels[$channel];
            }

            yield $this->send(Event::subscribeTo($channel));

            $closeFn = coroutine(function () use ($channel) {
                yield $this->send(Event::unsubscribeFrom($channel));
                unset($this->channels[$channel]);
            });

            return $this->channels[$channel] = new Channel($channel, $this->conn, \Closure::fromCallable($closeFn));
        })();
    }

    /** @return Promise<void> */
    public function send(Event|array $message): Promise
    {
        return $this->conn->send(\json_encode($message));
    }

    /** @return Promise<void> */
    public function close(): Promise
    {
        return coroutine(function () {
            foreach ($this->channels as $channel) {
                yield $channel->close();
            }
            return yield $this->conn->close();
        })();
    }
}
