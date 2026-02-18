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

### Chrome/Chromium use
```php
require "vendor/autoload.php";

use Wert1209yt\Browser\Browser;
use Swoole\Coroutine;

Coroutine\run(function () {

    $browser = Browser::launch([
        "browser" => "chromium", // you can "chrome" or "chromium"
        "chrome_command" => "chromium", // Your command to start to Chrome/Chromium, default "chromium"
        "debug_port" => 9222, // Debug port: Use any port that is not blocked by the system, default 9222
        "profile_path" => "/profile", // Profile: profile to save cookies, default "/generated/profile"
        "headless" => true // Headless mode: true/false, default false
    ]);

    $page = $browser->newPage();

    $page->navigate("https://example.com"); // Navigating to site
    $page->screenshot("example.png"); // Create screenshot from site page

});
```
### Firefox use
```php
require "vendor/autoload.php";

use Wert1209yt\Browser\Browser;
use Swoole\Coroutine;

Coroutine\run(function () {

    $browser = Browser::launch([
        "browser" => "firefox", // Firefox of course
        "firefox_command" => "firefox", // Your command to start to Firefox
        "debug_port" => 9222, // Debug port: Use any port that is not blocked by the system, default 9222
        "profile_path" => "/profile", // Profile: profile to save cookies, default "/generated/profile"
        "headless" => true // Headless mode: true/false, default false
    ]);

    $page = $browser->newPage();

    $page->navigate("https://example.com"); // Navigating to site
    $page->screenshot("example.png"); // Create screenshot from site page

});
```

>[!NOTE]
> You can use HTTP or SOCKS5 proxy, just add in config (launch). Example: `"proxy" => ["url" => "http://127.0.0.1"]`

### Methods:
- universal element() (Recommended)
  - Universal element controller
  - `$page->element(".btn")->click();`
  - `$page->element("#login")->type("hello");`
  - `$page->element(".item", 2)->click();`
- waitUntilLoaded()
  - Wait until page loaded
- clickOnButton()
  - Click on button
  - Example `$page->clickOnButton(["class" => "signinbutton", "id" => "signin"]);` + you can add "index" argument, default "index" is 0
- insertInInput()
  - Insert text to input box
  - Example `$page->insertInInput(["class" => "signinbutton", "id" => "signin", "text" => "I'm using php-autonomous-browser"]);`
- pressKey()
  - Press keyboard key
  - Example `$page->pressKey('a')`
- holdKey()
  - Hold keyboard key
  - Example `$page->holdKey('a', 4)`, 4 is seconds