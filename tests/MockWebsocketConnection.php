<?php declare(strict_types=1);

namespace Tests;

use Amp\Http\Client\Response;
use Amp\Cancellation;
use Amp\Socket\SocketAddress;
use Amp\Socket\TlsInfo;
use Amp\ByteStream\ReadableStream;
use Amp\Http\Client\Request;
use Amp\Socket\SocketAddressType;
use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\WebsocketCloseCode;
use Amp\Websocket\WebsocketMessage;
use Amp\Websocket\WebsocketCloseInfo;
use Amp\Websocket\WebsocketCount;
use Amp\Websocket\WebsocketTimestamp;
use ArrayIterator;
use Closure;
use IteratorAggregate;
use Traversable;

final class MockWebsocketConnection implements IteratorAggregate, WebsocketConnection
{
    private bool $closed = false;
    private int $position = 0;
    private int $sendCount = 0;
    /** @var \Amp\Websocket\WebsocketMessage[] $messages */
    private array $messages;

    /**
     * @param string[] $messages
     */
    public function __construct(
        array $messages,
        private ?\Closure $sendHandler = null,
        private ?\Closure $onCloseHandler = null,
    ) {
        $this->messages = array_map(fn (string $message) => WebsocketMessage::fromText($message), $messages);
    }

    public function getHandshakeResponse(): Response
    {
        return new Response('1.1', 200, null, [], null, new Request(''));
    }

    public function receive(?Cancellation $cancellation = null): ?WebsocketMessage
    {
        if ($this->closed || $this->position > count($this->messages)) {
            return null;
        }
        return $this->messages[$this->position++];
    }

    public function getId(): int
    {
        return 1;
    }

    public function getLocalAddress(): SocketAddress
    {
        return new class implements SocketAddress {
            public function toString(): string
            {
                return '';
            }

            public function getType(): SocketAddressType
            {
                return SocketAddressType::Internet;
            }
        };
    }

    public function getRemoteAddress(): SocketAddress
    {
        return new class implements SocketAddress {
            public function toString(): string
            {
                return '';
            }

            public function getType(): SocketAddressType
            {
                return SocketAddressType::Internet;
            }
        };
    }

    public function getTlsInfo(): ?TlsInfo
    {
        return null;
    }

    public function getCloseInfo(): WebsocketCloseInfo
    {
        return new WebsocketCloseInfo(500, '', 0, false);
    }

    public function isCompressionEnabled(): bool
    {
        return false;
    }

    public function sendText(string $data): void
    {
        if (!$this->closed && isset($this->sendHandler)) {
            ($this->sendHandler)($data, $this->sendCount++);
        }
    }

    public function sendBinary(string $data): void
    {
        if (!$this->closed && isset($this->sendHandler)) {
            ($this->sendHandler)($data, $this->sendCount++);
        }
    }

    public function streamText(ReadableStream $stream): void
    {
    }

    public function streamBinary(ReadableStream $stream): void
    {
    }

    public function ping(): void
    {
    }

    public function getCount(WebsocketCount $type): int
    {
        return count($this->messages);
    }

    public function getTimestamp(WebsocketTimestamp $type): float
    {
        return 0;
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function close(int $code = WebsocketCloseCode::NORMAL_CLOSE, string $reason = ''): void
    {
        $this->closed = true;
        if (isset($this->onCloseHandler)) {
            ($this->onCloseHandler)();
        }
    }

    public function onClose(Closure $onClose): void
    {
        $this->onCloseHandler = $onClose;
    }

    public function getIterator(): Traversable
    {
        for ($this->position; $this->position < count($this->messages); $this->position++) {
            yield $this->messages[$this->position];
        }
    }
}
