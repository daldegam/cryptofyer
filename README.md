CryptoFyer 0.1
==============

A unified framework to connect to different Crypto Exchange websites.

I am NOT associated, I repeat NOT associated to any Exchange website. Please use at your OWN risk.

Want to help me? You can tip me :)
* BTC: 1B27qUNVjKSMwfnQ2oq9viDY1hE3JY6XmQ

Supported Exchanges
----
* Bittrex (https://www.bittrex.com/)
* Cryptopia (https://www.cryptopia.co.nz/)

Installation
----

fetch the project via git:
```sh
$ git clone https://github.com/fransyozef/cryptofyer
```


Config.inc.php
----
Each exchange sits in its own folder and there you'll find 'config.example.inc.php'.
* Rename 'config.example.inc.php' to config.inc.php.
* Edit your key and secret in config.inc.php.


Required functions
----
The exchange classes have some required functions to implement:
* buy() -> place a buy order
* sell() -> place a sell order
* getOrders() -> get open orders
* cancel() -> cancel order
* getTicker() -> get currency information
* getCurrencyUrl() -> get the exchange currency detail url

Todo
----
* More Exchanges Api
* Better unified functions/notations
* Cleanup code
* Better documentation
