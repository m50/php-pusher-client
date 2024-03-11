<?php declare(strict_types=1);

namespace m50\Pusher;

use Amp\Promise;
use JsonSerializable;

use function Amp\coroutine;
use Amp\Websocket\Message as WebsocketMessage;

final class Event implements JsonSerializable
{
    public function __construct(
        public string $event,
        public array $data = [],
        public string $channel = ''
    ) {
    }

    /** @return Promise<Event> */
    public static function fromWebsocketMessage(WebsocketMessage $message): Promise
    {
        return coroutine(function () use ($message) {
            return static::jsonDeserialize(yield $message->buffer());
        })();
    }

    public static function jsonDeserialize(string $message): static
    {
        $message = json_decode($message, true, flags: JSON_THROW_ON_ERROR);

        return new static(
            $message['event'],
            \is_array($message['data']) ? $message['data'] : \json_decode($message['data'], true),
            $message['channel'] ?? ''
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'event' => $this->event,
            'data' => $this->data,
            'channel' => $this->channel,
        ];
    }

    public static function isError(Event $event): bool
    {
        return $event->event === 'pusher:error';
    }

    public static function notSubscriptionSucceeded(Event $event): bool
    {
        return $event->event !== 'pusher_internal:subscription_succeeded';
    }

    public static function connectionEstablished(Event $event): bool
    {
        return $event->event === 'pusher:connection_established';
    }

    public static function subscribeTo(string $channel): static
    {
        return new Event('pusher:subscribe',  ['channel' => $channel]);
    }

    public static function unsubscribeFrom(string $channel): static
    {
        return new Event('pusher:unsubscribe',  ['channel' => $channel]);
    }

    public static function ping(): static
    {
        return new Event('pusher:ping');
    }
}
