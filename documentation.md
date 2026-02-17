# Documentation

## How to install
1. Download swoole
  - `pecl install swoole` — or any other installation methods
2. Check if the Composer is installed
  - if Composer no installed, use `composer init` in your project
3. Install PHP autonomous browser
  - `composer require wert1209yt/php-autonomous-browser`

---

## Simple use

```php
require "vendor/autoload.php";

use Wert1209yt\Browser\Browser;
use Swoole\Coroutine;

Coroutine\run(function () {

    $browser = Browser::launch([
        "chrome_path" => "/usr/bin/chromium", // Your path
        "debug_port" => 9222 // Debug port: Use any port that is not blocked by the system.
    ]);

    $page = $browser->newPage();

    $page->navigate("https://example.com"); // Navigating to site
    $page->screenshot("example.png"); // Create screenshot from site page

});
```