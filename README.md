CryptoFyer 0.2
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

Market/currency pair
----
When I started with this unified api platform, I used Bittrex's API as a model.
Bittrex's string literal for the marketpair is [market]-[currency] for example : BTC-ETH.

After Bittrex set out to implement Cryptopia's API. Cryptopia's string literal for the marketpair is [currency]-[market] for example : ETH-BTC.

In order to normalize the market literal string you can use the getMarketPair() function.

```php
$_market = "USDT";
$_currency = "BTC";

$exchange  = new BittrexxApi($apiKey , $apiSecret );
$market   = $exchange->getMarketPair($_market , $_currency);
```
Here you see '$market' has the value 'USDT-BTC'.

```php
$_market = "USDT";
$_currency = "BTC";

$exchange  = new CryptopiaApi($apiKey , $apiSecret );
$market   = $exchange->getMarketPair($_market , $_currency);
```
Here you see '$market' has the value 'BTC-USDT'.

In the future, each exchange api class has a 'getMarketPair()' function to retrieve the right pair.


Todo
----
* More Exchanges Api
* Better unified functions/notations
* Cleanup code
* Better documentation
