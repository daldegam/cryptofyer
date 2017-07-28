CryptoFyer Coinexchange v0.4
==============

PHP client api for Coinexchange

I am NOT associated, I repeat NOT associated to Coinexchange. Please use at your OWN risk.

Want to help me? You can tip me :)
* BTC: 1B27qUNVjKSMwfnQ2oq9viDY1hE3JY6XmQ


Exchange Documentation
----
Coinexchange API documentation: http://coinexchangeio.github.io/slate/

Prerequisite
----
* PHP 5.3.x
* Curl
* Valid api token at Coinexchange


Config.inc.php
----
* Rename 'config.example.inc.php' to config.inc.php.
* Edit your key and secret in config.inc.php.



Example
----
```php
$exchange  = new CoinexchangeApi($apiKey , $apiSecret );
$result = $exchange->getBalance(array("currency" => "BTC"));
```
