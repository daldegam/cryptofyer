CryptoFyer Kraken v0.1
==============

PHP client api for Kraken api v0.1

I am NOT associated, I repeat NOT associated to Kraken. Please use at your OWN risk.

Want to help me? You can tip me :)
* BTC: 1B27qUNVjKSMwfnQ2oq9viDY1hE3JY6XmQ


Exchange Documentation
----
Exchange API documentation:

Prerequisite
----
* PHP 5.3.x
* Curl
* Valid api token at Exchange


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
