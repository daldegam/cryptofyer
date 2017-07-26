CryptoFyer Kraken v0.1
==============

PHP client api for Kraken api

I am NOT associated, I repeat NOT associated to Kraken. Please use at your OWN risk.

Want to help me? You can tip me :)
* BTC: 1B27qUNVjKSMwfnQ2oq9viDY1hE3JY6XmQ


Exchange Documentation
----
Kraken API documentation: https://www.kraken.com/help/api

Prerequisite
----
* PHP 5.3.x
* Curl
* Valid api token at Kraken


Config.inc.php
----
* Rename 'config.example.inc.php' to config.inc.php.
* Edit your key and secret in config.inc.php.



Example
----
```php
$exchange  = new KrakenApi($apiKey , $apiSecret );
$result = $exchange->getBalance(array("currency" => "BTC"));
```
