<?php declare(strict_types=1);

namespace Tests;

use m50\Pusher\Client;
use m50\Pusher\Event;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    public function testClientListenToChannel(): void
    {
        $client = new Client(new MockWebsocketConnection(
            [
                json_encode(new Event('WRONG-TEST', ['test' => false], 'wrong-test-channel')),
                json_encode(new Event('TEST', ['test' => true], 'test-channel')),
            ],
            function (string $message, int $time) {
                $event = Event::jsonDeserialize($message);
                match ($time) {
                    0 => $this->assertEquals('pusher:subscribe', $event->event),
                    1 => $this->assertEquals('pusher:unsubscribe', $event->event),
                    default => $this->fail('Shouldn\'t have gotten here'),
                };
            }
        ));

        $event = $client->channel('test-channel')->next();
        $this->assertEquals('TEST', $event->event);
        $this->assertEquals(['test' => true], $event->data);
    }

    public function testReadFromClientNotChannel(): void
    {
        $client = new Client(new MockWebsocketConnection([
            json_encode(new Event('TEST1', ['test' => true])),
            json_encode(new Event('TEST2', ['test' => true])),
        ]));

        foreach ($client as $i => $event) {
            $this->assertInstanceOf(Event::class, $event);
            $this->assertEquals($i === 0 ? 'TEST1' : 'TEST2', $event->event);
            $this->assertEquals(['test' => true], $event->data);
        }
    }

    public function testChannelSubscribeAndThenUnsubscribe(): void
    {
        $client = new Client(new MockWebsocketConnection([
            json_encode(new Event('TEST', [], 'channel-1')),
            json_encode(new Event('TEST', [], 'channel-1')),
            json_encode(new Event('TEST', [], 'channel-1')),
            json_encode(new Event('TEST', [], 'channel-1')),
        ]));

        $channel = $client->channel('channel-1');

        $loop = 0;
        $channel->subscribe(function (Event $event) use (&$loop, $channel) {
            $this->assertEquals('TEST', $event->event);
            $loop++;
            if ($loop >= 2) {
                $channel->unsubscribe();
            }
        });

        $this->assertEquals(2, $loop);
    }
}
