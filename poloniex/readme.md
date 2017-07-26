CryptoFyer Poloniex v0.1
==============

PHP client api for Poloniex

I am NOT associated, I repeat NOT associated to Poloniex. Please use at your OWN risk.

Want to help me? You can tip me :)
* BTC: 1B27qUNVjKSMwfnQ2oq9viDY1hE3JY6XmQ


Exchange Documentation
----
Poloniex API documentation: https://poloniex.com/support/api/

Prerequisite
----
* PHP 5.3.x
* Curl
* Valid api token at Poloniex


Config.inc.php
----
* Rename 'config.example.inc.php' to config.inc.php.
* Edit your key and secret in config.inc.php.



Example
----
```php
$exchange  = new PoloniexApi($apiKey , $apiSecret );
$result = $exchange->getBalance(array("currency" => "BTC"));
```
