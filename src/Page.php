<?php

namespace Wert1209yt\Browser;

use Wert1209yt\Browser\CDP\Connection;

class Page
{
    public function __construct(private Connection $cdp)
    {
        $cdp->send("Page.enable");
        $cdp->send("Runtime.enable");
        $cdp->send("DOM.enable");
    }

    public function navigate(string $url): void
    {
        $this->cdp->send("Page.navigate", ["url" => $url]);
        $this->waitUntilLoaded();
    }

    private function waitUntilLoaded(): void
    {
        for ($i = 0; $i < 40; $i++) {
            $res = $this->cdp->send("Runtime.evaluate", [
                "expression" => "document.readyState",
                "returnByValue" => true
            ]);

            if (($res['result']['result']['value'] ?? null) === "complete") {
                return;
            }

            \Swoole\Coroutine::sleep(0.25);
        }

        throw new \RuntimeException("Page load timeout");
    }

    public function click(string $selector): void
    {
        $this->cdp->send("Runtime.evaluate", [
            "expression" => "
                const el = document.querySelector('$selector');
                if (!el) throw 'Element not found';
                el.scrollIntoView();
                el.click();
            "
        ]);
    }

    public function type(string $selector, string $text): void
    {
        $this->cdp->send("Runtime.evaluate", [
            "expression" => "
                const el = document.querySelector('$selector');
                if (!el) throw 'Element not found';
                el.focus();
                el.value = '$text';
                el.dispatchEvent(new Event('input', { bubbles: true }));
            "
        ]);
    }

    public function screenshot(string $file): void
    {
        $res = $this->cdp->send("Page.captureScreenshot", [
            "format" => "png"
        ]);

        file_put_contents($file, base64_decode($res['result']['data']));
    }
}