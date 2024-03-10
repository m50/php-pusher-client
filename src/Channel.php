<?php declare(strict_types=1);

namespace m50\Pusher;

use Amp\TimeoutCancellation;
use Amp\Websocket\Client\WebsocketConnection;
use m50\Pusher\Exceptions\PusherException;

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
     * @param (callable(Event $event): void) $onEvent
     * @return void
     */
    public function subscribe(callable $onEvent, ?callable $onError = null): void
    {
        foreach ($this->conn as $message) {
            $event = Event::fromWebsocketMessage($message);
            if ($onError !== null && Event::isError($event)) {
                $onError(PusherException::fromEvent($event));
            } elseif ($this->filterEvent($event)) {
                $onEvent($event);
            }

            if (!$this->continueListening) {
                $this->close();
                return;
            }
        }
    }

    /**
     * Wait for the next event that belongs to channel.
     * @param float $timeout Seconds before cancellation.
     */
    public function next(?float $timeout = null): Event
    {
        while ($message = $this->conn->receive($timeout ? $this->getTimeout($timeout) : null)) {
            $event = Event::fromWebsocketMessage($message);
            if (Event::isError($event)) {
                throw PusherException::fromEvent($event);
            } elseif ($this->filterEvent($event)) {
                return $event;
            }
        }

        throw PusherException::unknown();
    }

    public function close(): void
    {
        $this->unsubscribe();
        ($this->closeCommand)();
    }

    private function filterEvent(Event $event): bool
    {
        return $event->channel === $this->name
            && Event::notSubscriptionSucceeded($event);
    }

    private function getTimeout(float $timeout): TimeoutCancellation
    {
        return new TimeoutCancellation(
            $timeout,
            "Next from channel {$this->name} timed out after {$timeout} seconds"
        );
    }
}
