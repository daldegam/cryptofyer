CryptoFyer 0.3
==============

A unified framework to connect to different Crypto Exchange websites.

I am NOT associated, I repeat NOT associated to any Exchange website. Please use at your OWN risk.

Want to help me? You can tip me :)
* BTC: 1B27qUNVjKSMwfnQ2oq9viDY1hE3JY6XmQ

Supported Exchanges
----
* Bittrex (https://www.bittrex.com/)
Official API documentation: https://bittrex.com/home/api

* Cryptopia (https://www.cryptopia.co.nz/)
Official Public API documentation: https://www.cryptopia.co.nz/Forum/Thread/255
Official Private API documentation: https://www.cryptopia.co.nz/Forum/Thread/256

API keys safety
----
All the exchanges uses API keys. Each API key consists of a public and a private key. NEVER and I repeat NEVER expose your api keys to anybody! If somebody has your API keys, this person can sell/buy/withdraw from you account!

If you do suspect somebody has your api keys DELETE your api keys at once!!!

Also, a lot of exchanges have the option to make you api keys more secure with the option to sell/buy/withdra option. So if you can have an api key with only read rights and no sell/buy/withdraw right. But that depends on the exchange.

One more time: NEVER EXPOSE YOUR API KEYS TO ANYBODY!!!!


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
* getOrder()  -> get order
* cancel() -> cancel order
* getTicker() -> get currency information
* getCurrencyUrl() -> get the exchange currency detail url
* getMarketHistory() -> get market history
* getBalance() -> get balance

Market/currency pair
----
When I started with this unified api platform, I used Bittrex's API as a model.
Bittrex's string literal for the marketpair is [market]-[currency] for example : BTC-ETH.

After Bittrex I implemented Cryptopia's API. Cryptopia's string literal for the marketpair is [currency]-[market] for example : ETH-BTC.

In order to normalize the market literal string you can use the getMarketPair() function.

```php
$_market = "USDT";
$_currency = "BTC";

$exchange  = new BittrexxApi($apiKey , $apiSecret );
$market   = $exchange->getMarketPair($_market , $_currency);
```
Here you see `$market` has the value 'USDT-BTC'.

```php
$_market = "USDT";
$_currency = "BTC";

$exchange  = new CryptopiaApi($apiKey , $apiSecret );
$market   = $exchange->getMarketPair($_market , $_currency);
```
Here you see `$market` has the value 'BTC-USDT'.

In the future, each exchange api class has a `getMarketPair()` function to retrieve the right pair notation.

Unified market arguments
----
Some functions requires the market string literal as argument. For example Bittrex's ticker:

```php
$result = $exchange->getTicker(array("market" => "BTC-ETH"));
debug($result);
```

or Cryptopia's ticker :

```php
$result = $exchange->getTicker(array("market" => "ETH-BTC"));
debug($result);
```

As you can see, the `market` value is different. To normalize, I added 2 special arguments :

* `_market`
* `_currency`

for example :

```php
$result = $exchange->getTicker(array("_market" => "BTC" , "_currency" => "ETH"));
debug($result);
```
The function will resolve the market pair with the `getMarketPair()` function.

Unified tests
----
In the `tests` folder you will find some examples where you can see the normalization of functions.  

Todo
----
* More Exchanges Api
* Better unified functions/notations
* Cleanup code
* Better documentation
