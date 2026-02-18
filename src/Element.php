<?php

namespace Wert1209yt\Browser;

use Wert1209yt\Browser\CDP\Connection;

class Element
{
    private Connection $connection;
    private string $selector;
    private int $index;
    private int $id = 0;

    public function __construct(Connection $connection, string $selector, int $index = 0)
    {
        $this->connection = $connection;
        $this->selector = $selector;
        $this->index = $index;
    }

    private function evaluate(string $js)
    {
        $this->id++;

        $this->connection->send([
            "id" => $this->id,
            "method" => "Runtime.evaluate",
            "params" => [
                "expression" => $js,
                "returnByValue" => true
            ]
        ]);
    }

    public function click(): void
    {
        $selector = addslashes($this->selector);

        $js = <<<JS
        (function(){
            let elements = document.querySelectorAll("$selector");
            if (elements.length > {$this->index}) {
                elements[{$this->index}].click();
                return true;
            }
            return false;
        })();
        JS;

        $this->evaluate($js);
    }

    public function type(string $text): void
    {
        $selector = addslashes($this->selector);
        $text = addslashes($text);

        $js = <<<JS
        (function(){
            let elements = document.querySelectorAll("$selector");
            if (elements.length > {$this->index}) {
                let el = elements[{$this->index}];
                el.value = "$text";
                el.dispatchEvent(new Event('input', { bubbles: true }));
                return true;
            }
            return false;
        })();
        JS;

        $this->evaluate($js);
    }

    public function getText()
    {
        $selector = addslashes($this->selector);

        $js = <<<JS
        (function(){
            let elements = document.querySelectorAll("$selector");
            if (elements.length > {$this->index}) {
                return elements[{$this->index}].innerText;
            }
            return null;
        })();
        JS;

        $this->evaluate($js);
    }
}