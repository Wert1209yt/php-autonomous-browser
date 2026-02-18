<?php

namespace Wert1209yt\Browser;

use Wert1209yt\Browser\CDP\Connection;
use Swoole\Coroutine\Http\Client;

class Browser
{
    private Connection $connection;

    public static function launch(array $config): self
    {
        $browser = $config['browser'] ?? 'chromium';

        $port = $config['debug_port'] ?? 9222;

        $profile = $config['profile_path'] ?? __DIR__ '/generated/profile';

        $headless = $config['headless'] ?? false;

        $cmd = self::selectBrowserCommand($config);

        shell_exec($cmd);

        \Swoole\Coroutine::sleep(1);

        $client = new Client("127.0.0.1", $port);
        $client->get("/json");

        $targets = json_decode($client->body, true);
        $wsUrl = $targets[0]['webSocketDebuggerUrl'];

        $u = parse_url($wsUrl);

        $connection = new Connection($u['host'], $u['port'], $u['path']);
        $connection->connect();

        $browser = new self();
        $browser->connection = $connection;

        return $browser;
    }

    public static selectBrowserCommand(array $config): string {

    $profile = $config['profile_path'] ?? __DIR__ . '/generated/profile';
    $headless = $config['headless'] ?? false;
    $port = $config['port'] ?? 9222;

    switch ($config['browser']) {
        case 'chromium':
        case 'chrome':
            $headlessFlag = $headless ? '--headless=new' : '';

            echo "{$config['chrome_command'] ?? "chromium} --remote-debugging-port=$port --user-data-dir={$profile} {$headlessFlag} > / 
            dev/.null 2>&1 &";  
            break;

        case 'firefox':
            $headlessFlag = $headless ? '--headless' : '';

            echo "{$config['firefox_command'] ?? "firefox"} --remote-debugging-port={$port} --profile {$profile} {$headlessFlag} > /dev/null 2>&1 &";
            break;
    }

    }

    public function newPage(): Page
    {
        return new Page($this->connection);
    }
}