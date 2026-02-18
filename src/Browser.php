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

        $profile = $config['profile_path'] ?? __DIR__ . '/generated/profile';

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

    private static function prepareFirefoxProfile(string $profilePath, string $proxy): void
    {
        if (!is_dir($profilePath)) {
            mkdir($profilePath, 0777, true);
        }

        $parts = parse_url($proxy);

        $host = $parts['host'] ?? '127.0.0.1';
        $port = $parts['port'] ?? 8080;
        $scheme = $parts['scheme'] ?? 'http';

        $proxyType = 1; // manual proxy

        $prefs = <<<PREFS
        user_pref("network.proxy.type", $proxyType);
        user_pref("network.proxy.http", "$host");
        user_pref("network.proxy.http_port", $port);
        user_pref("network.proxy.ssl", "$host");
        user_pref("network.proxy.ssl_port", $port);
        user_pref("network.proxy.no_proxies_on", "");
        PREFS;

        file_put_contents($profilePath . "/prefs.js", $prefs);
    }

    private static function selectBrowserCommand(array $config): string
    {

    $profile = $config['profile_path'] ?? __DIR__ . '/generated/profile';
    $headless = $config['headless'] ?? false;
    $port = $config['debug_port'] ?? 9222;

    $proxy = $config['proxy']['url'] ?? null;

    switch ($config['browser']) {
        case 'chromium':
        case 'chrome':
            $headlessFlag = $headless ? '--headless=new' : '';
            $proxyFlag = $proxy ? "--proxy-server=$proxy" : '';

            return "{$config['chrome_command'] ?? "chromium}
            --remote-debugging-port=$port
            --user-data-dir={$profile}
            {$proxyFlag}
            {$headlessFlag} > / 
            dev/.null 2>&1 &";  
            break;

        case 'firefox':
            $headlessFlag = $headless ? '--headless' : '';

            if ($proxy) {
                self::prepareFrefixProfile($profile, $proxy);
            }

            return "{$config['firefox_command'] ?? "firefox"}
            --remote-debugging-port={$port}
            --profile {$profile} 
            {$proxyFlag}
            {$headlessFlag} > /dev/null 2>&1 &";
            break;
    }

    }

    public function newPage(): Page
    {
        return new Page($this->connection);
    }
}