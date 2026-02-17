<?php

namespace Wert1209yt\Browser\CDP;

use Swoole\Coroutine\Http\Client;
use Swoole\Coroutine\Channel;

class Connection
{
    private Client $ws;
    private int $id = 0;
    private array $waiting = [];

    public function __construct(
        private string $host,
        private int $port,
        private string $path
    ) {}

    public function connect(): void
    {
        $this->ws = new Client($this->host, $this->port);

        if (!$this->ws->upgrade($this->path)) {
            throw new \RuntimeException("WebSocket connection failed");
        }

        go(function () {
            while (true) {
                $frame = $this->ws->recv();
                if (!$frame) break;

                $data = json_decode($frame->data, true);

                if (isset($data['id']) && isset($this->waiting[$data['id']])) {
                    $this->waiting[$data['id']]->push($data);
                    unset($this->waiting[$data['id']]);
                }
            }
        });
    }

    public function send(string $method, array $params = [], float $timeout = 5): array
    {
        $id = ++$this->id;
        $channel = new Channel(1);
        $this->waiting[$id] = $channel;

        $this->ws->push(json_encode([
            'id' => $id,
            'method' => $method,
            'params' => $params
        ]));

        $result = $channel->pop($timeout);

        if (!$result) {
            throw new \RuntimeException("Timeout on $method");
        }

        return $result;
    }
}