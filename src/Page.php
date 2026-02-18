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

    public function evaluate(string $expression)
    {
        $this->send("Runtime.evaluate", [
            "expression" => $expression,
            "returnByValue" => true
        ]);
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

    public function pressKey(string $key): void
    {
        $this->id++;

    // keyDown
        $this->connection->send([
            "id" => $this->id,
            "method" => "Input.dispatchKeyEvent",
            "params" => [
                "type" => "keyDown",
                "key" => $key,
                "code" => "Key" . strtoupper($key),
                "windowsVirtualKeyCode" => ord(strtoupper($key)),
                "nativeVirtualKeyCode" => ord(strtoupper($key))
            ]
        ]);

        $this->id++;

    // keyUp
        $this->connection->send([
            "id" => $this->id,
            "method" => "Input.dispatchKeyEvent",
            "params" => [
                "type" => "keyUp",
                "key" => $key,
                "code" => "Key" . strtoupper($key),
                "windowsVirtualKeyCode" => ord(strtoupper($key)),
                "nativeVirtualKeyCode" => ord(strtoupper($key))
            ]
        ]);
    }

    public function holdKey(string $key, float $seconds = 1): void
    {
        $this->id++;

        $this->connection->send([
            "id" => $this->id,
            "method" => "Input.dispatchKeyEvent",
            "params" => [
                "type" => "keyDown",
                "key" => $key,
                "code" => "Key" . strtoupper($key),
                "windowsVirtualKeyCode" => ord(strtoupper($key)),
                "nativeVirtualKeyCode" => ord(strtoupper($key))
            ]
        ]);

        \Swoole\Coroutine::sleep($seconds);

        $this->id++;

        $this->connection->send([
            "id" => $this->id,
            "method" => "Input.dispatchKeyEvent",
            "params" => [
                "type" => "keyUp",
                "key" => $key,
                "code" => "Key" . strtoupper($key),
                "windowsVirtualKeyCode" => ord(strtoupper($key)),
                "nativeVirtualKeyCode" => ord(strtoupper($key))
            ]
        ]);
    }

    public function clickOnButton(array $buttonInfo): void
    {
        $class = $buttonInfo['class'];
        $id = $buttonInfo['id'] ?? false;
        $index = $buttonInfo['index'] ?? 0;

        $js = <<<JS
            (function(){
                let elements = document.querySelectorAll(".$class");

                if ($id) {
                    let el = document.querySelector(".$class#$id");
                    if (el) { el.click(); return true; }
                    return false;
                }

                if (elements.length > $index) {
                    elements[$index].click();
                    return true;
                }

                return false;
            })();
        JS;

        $this->evaluate($js);
    }

    public function insertInInput(array $inputInfo): void
    {
        $class = $inputInfo['class'];
        $text = addslashes($inputInfo['text']);
        $id = $inputInfo['id'];
        $index = $inputInfo['index'] ?? 0;

        $js = <<<JS
            (function(){
                let elements = document.querySelectorAll(".$class");

                if ($id) {
                    let el = document.querySelector(".$class#$id");
                    if (el) {
                        el.value = "$text";
                        el.dispatchEvent(new Event('input', { bubbles: true }));
                        return true;
                    }
                    return false;
                }

                if (elements.length > $index) {
                    elements[$index].value = "$text";
                    elements[$index].dispatchEvent(new Event('input', { bubbles: true }));
                    return true;
                }

                return false;
            })();
        JS;

        $this->evaluate($js);
    }

    public function element(string $selector, int $index = 0): Element
    {
        return new Element($this->connection, $selector, $index);
    }

    public function screenshot(string $file): void
    {
        $res = $this->cdp->send("Page.captureScreenshot", [
            "format" => "png"
        ]);

        file_put_contents($file, base64_decode($res['result']['data']));
    }
}