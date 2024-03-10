<?php

declare(strict_types=1);

namespace m50\Pusher;

final class ApiSettings
{
    private const VERSION = '0.0.1';
    /**
     * Create Pusher compatible version.
     *
     * @param  string $version
     * @return string
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }

    /**
     * Create WebSocket URL for given App ID.
     *
     * @param  string $appId
     * @return string
     */
    public static function createUrl(string $appId, ?string $cluster = null): string
    {
        $query = \http_build_query([
            'client' => 'm50/pusher (https://github.com/m50/php-pusher-client)',
            'protocol' => 7,
            'version' => static::getVersion(),
        ]);

        $host = ($cluster !== null) ? "ws-{$cluster}.pusher.com" : 'ws.pusherapp.com';

        return "wss://{$host}/app/{$appId}?{$query}";
    }
}
