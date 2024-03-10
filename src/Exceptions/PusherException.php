<?php declare(strict_types=1);

namespace m50\Pusher\Exceptions;

use m50\Pusher\Event;

final class PusherException extends \Exception
{
    public static function fromEvent(Event $event): static
    {
        return new static($event->data['message'], $event->data['code']);
    }

    public static function unknown(): static
    {
        return new static('Unknown error occured', 500);
    }
}
