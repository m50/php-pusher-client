<?php declare(strict_types=1);

namespace m50\Pusher;

use Amp\Promise;
use Amp\Websocket\Client\Connection as WebsocketConnection;
use m50\Pusher\Exceptions\PusherException;

use function Amp\coroutine;

final class Channel
{
    private $continueListening = true;

    public function __construct(
        private string $name,
        private WebsocketConnection $conn,
        private \Closure $closeCommand,
    ) {
    }

    public function unsubscribe()
    {
        $this->continueListening = false;
    }

    /**
     * Execute an action on every event that belongs to the channel.
     *
     * @param (callable(Event): void) $onEvent
     * @param (callable(\Throwable): void)|null $onError
     * @return Promise<void>
     */
    public function subscribe(callable $onEvent, ?callable $onError = null): Promise
    {
        return coroutine(function () use ($onEvent, $onError) {
            while ($message = yield $this->conn->receive()) {
                $event = yield Event::fromWebsocketMessage($message);
                if ($onError !== null && Event::isError($event)) {
                    $onError(PusherException::fromEvent($event));
                } elseif ($this->filterEvent($event)) {
                    $onEvent($event);
                }

                if (!$this->continueListening) {
                    yield $this->close();
                    return;
                }
            }
        })();
    }

    /** @return Promise<Event> */
    public function next(): Promise
    {
        return coroutine(function () {
            while ($message = yield $this->conn->receive()) {
                $event = yield Event::fromWebsocketMessage($message);
                if (Event::isError($event)) {
                    throw PusherException::fromEvent($event);
                } elseif ($this->filterEvent($event)) {
                    return $event;
                }
            }

            throw PusherException::unknown();
        })();
    }

    public function close(): Promise
    {
        $this->unsubscribe();
        return ($this->closeCommand)();
    }

    private function filterEvent(Event $event): bool
    {
        return $event->channel === $this->name
            && Event::notSubscriptionSucceeded($event);
    }
}
