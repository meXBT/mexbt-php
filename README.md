# mexbt-php

PHP client for the [meXBT](https://mexbt.com) exchange API. All functions return
the JSON as-is.

## Install

Require mexbt.php in your project.

## PHP version

PHP 5.4 tested. Lower versions may work.

## Public API

You can access all the Public API functions with zero configuration. By default they will
use the 'BTCMXN' currency pair.

```php
$ctx = new mexbt();

$ctx->ticker();
$ctx->order_book();
$ctx->public_trades();
$ctx->trades_by_date("btcmxn", 1435658153, 1435678153);
```

You can give the currency pair to each function, or change the default by:

```php
$ctx->currency_pair = "btcusd";
```

## Private API

### API Keys

You need to generate an API key pair at https://mexbt.com/api/keys. However if you want to
get started quickly we recommend having a play in the sandbox first, see the 'Sandbox'
section below.

### Configuration

Set the used keys and e-mail address:

```php
$ctx->public_key = "aa";
$ctx->private_key = "aa";
$ctx->user_id = "aa@aa.com"; # Your registered e-mail
$ctx->sandbox = true;
```

### Order functions

```php
create_order
cancel_all_orders
```

### Account functions

```php
balance
trades
orders
withdraw
info
deposit_addresses
deposit_address("BTC")
```

### Sandbox

It's a good idea to first play with the API in the sandbox, that way you don't need any real
money to start trading with the API. Just make sure you configure `sandbox = true`.

You can register a sandbox account at https://sandbox.mexbt.com/en/register. It will ask you
to validate your email but there is no need, you can login right away at
https://sandbox.mexbt.com/en/login. Now you can setup your API keys at
https://sandbox.mexbt.com/en/api/keys.

Your sandbox account will automatically have a bunch of cash to play with.

## API documentation

You can find API docs for the Public API at http://docs.mexbtpublicapi.apiary.io

API docs for the Private API are at http://docs.mexbtprivateapi.apiary.io

There are also docs for the Private API sandbox at
http://docs.mexbtprivateapisandbox.apiary.io
