CryptoFyer Cryptopia v0.5
==============

PHP client api for Cryptopia

I am NOT associated, I repeat NOT associated to Cryptopia. Please use at your OWN risk.

Want to help me? You can tip me :)
* BTC: 1B27qUNVjKSMwfnQ2oq9viDY1hE3JY6XmQ


Cryptopia Documentation
----
* Documentation Public Api : https://www.cryptopia.co.nz/Forum/Thread/255
* Documentation Private Api : https://www.cryptopia.co.nz/Forum/Thread/256

Prerequisite
----
* PHP 5.3.x
* Curl
* Valid api token at Cryptopia


Config.inc.php
----
* Rename 'config.example.inc.php' to config.inc.php.
* Edit your key and secret in config.inc.php.

Public API functions
----
TODO

Private API functions
----
TODO



Example
----
```php
$cryptopia  = new CryptopiaApi($apiKey , $apiSecret );
$result = $cryptopia->getBalance(array("currency" => "BTC"));
```

Example web files
----
TODO

Example CLI files
----
I've prepared some example files to get you started in the examples folder to run from your command line.
* cli_watch.php (command line script to watch a currency)
* cli_console.php (command line console like to sell/buy/cancel orders)
