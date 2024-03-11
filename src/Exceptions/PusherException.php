<?php declare(strict_types=1);

namespace m50\Pusher\Exceptions;

use m50\Pusher\Event;

final class PusherException extends \Exception
{
    private ?Event $event = null;

    public static function fromEvent(Event $event): static
    {
        $ret = new static($event->data['message'], $event->data['code']);
        $ret->event = $event;

        return $ret;
    }

    public static function unknown(): static
    {
        return new static('Unknown error occured', 500);
    }

    public function getEvent(): Event
    {
        return $this->event;
    }
}
