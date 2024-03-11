# Pusher Client for php8.0

> Note: This branch is for php8.0, and is using outdated libraries and has no tests.
> This is for compatibility only and should not be used normally.

## Installation

```bash
composer require m50/pusher-client
```

## Usage

```php
$client = Client::create('my-app', 'us3');
$channel = $client->channel('my-channel');

while ($event = $channel->next(300)) {
    var_dump($event);
}
```

For more examples see the [examples directory](examples).

## Features

Async-first, but works in synchronous applications as well thanks to Amphp's implementations.

Is just a basic implementation of Pusher's feature set, as necessary for other projects.

PRs welcome for additional features.

## License

MIT License

Copyright (c) 2024 Marisa

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
