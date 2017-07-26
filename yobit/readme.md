CryptoFyer Yobit v0.2
==============

PHP client api for Yobit

I am NOT associated, I repeat NOT associated to Yobit. Please use at your OWN risk.

Want to help me? You can tip me :)
* BTC: 1B27qUNVjKSMwfnQ2oq9viDY1hE3JY6XmQ


Exchange Documentation
----
Yobit API documentation: https://yobit.net/en/api/

Prerequisite
----
* PHP 5.3.x
* Curl
* Valid api token at Yobit


Config.inc.php
----
* Rename 'config.example.inc.php' to config.inc.php.
* Edit your key and secret in config.inc.php.



Example
----
```php
$exchange  = new YobitApi($apiKey , $apiSecret );
$result = $exchange->getBalance(array("currency" => "BTC"));
```
