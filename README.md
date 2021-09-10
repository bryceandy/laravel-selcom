<p align="center"><img src="https://bryceandy.com/selcom.png" width="400"></p>

# Selcom package for Laravel apps

[![Actions Status](https://github.com/bryceandy/laravel-selcom/workflows/Tests/badge.svg)](https://github.com/bryceandy/laravel-selcom/actions)
<a href="https://packagist.org/packages/bryceandy/laravel-selcom"><img src="https://poser.pugx.org/bryceandy/laravel-selcom/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/bryceandy/laravel-selcom"><img src="https://poser.pugx.org/bryceandy/laravel-selcom/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/bryceandy/laravel-selcom"><img src="https://poser.pugx.org/bryceandy/laravel-selcom/license.svg" alt="License"></a>

This package enables Laravel developers to integrate their websites/APIs with all Selcom API services

## Installation

Pre-installation requirements

* Supports Laravel projects starting version 8.*
* Minimum PHP version is 7.4
* Your server must have the cURL PHP extension (ext-curl) installed

Then proceed to install:

```
composer require bryceandy/laravel-selcom
```

## Configuration

To access Selcom's APIs, you will need to provide the package with access to your Selcom vendorID, API Key and Secret Key.

After obtaining the three credentials from Selcom support, add their values in the `.env` variables:

```dotenv
SELCOM_VENDOR_ID=123456
SELCOM_API_KEY=yourApiKey
SELCOM_API_SECRET=yourSecretKey

SELCOM_IS_LIVE=false
```

Note that when starting you will be provided with test credentials.

When you change to live credentials don't forget to change `SELCOM_IS_LIVE` to `true`.

We are going to update more configuration settings as we move along, but feel free to publish the config to fully customize it.

```
php artisan vendor:publish --tag=selcom-config
```

## Checkout API

Checkout is the simplest Selcom API we can start processing payments with.

### Checkout payments using USSD

This API automatically interacts with your user's USSD directly after being called.

**Note**: As of now, direct USSD is only applicable to AitelMoney and TigoPesa customers.

```php
use Bryceandy\Selcom\Facades\Selcom;

Selcom::checkout([
    'name' => "Buyer's name", 
    'email' => "Buyer's email",
    'phone' => "Buyer's msisdn, for example 255756334000",
    'amount' => "Amount to be paid",
    'transaction_id' => "Unique transaction id",
    'is_ussd' => true,
]);
```

Other networks may use USSD only manually with tokens as shown with other checkout options below.

### Checkout to the payments page (without cards)

The payment page contains payment options such as QR code, all mobile money options etc.

To redirect to this page, we will use the previous example but return without the `is_ussd` option:

```php
use Bryceandy\Selcom\Facades\Selcom;

return Selcom::checkout([
    'name' => "Buyer's name", 
    'email' => "Buyer's email",
    'phone' => "Buyer's msisdn, for example 255756334000",
    'amount' => "Amount to be paid",
    'transaction_id' => "Unique transaction id",
]);
```

Optionally, you may specify using the `.env` file the following:

 - The page where your users will be redirected once they complete a payment:

```dotenv
SELCOM_REDIRECT_URL=https://mysite.com/selcom/redirect
```

 - The page where your users will be taken when they cancel the payment process:

```dotenv
SELCOM_CANCEL_URL=https://mysite.com/selcom/cancel
```

If you feel lazy, this package already ships with these pages for you. And if you want to customize them, run:

```
php artisan vendor:publish --tag=selcom-views
```

 - Also, you can assign a prefix for the package. This will be applied to the routes and order IDs

```dotenv
SELCOM_PREFIX=SHOP
```

### Checkout to the payments page (with cards)

To use the cards on the payment page,

#### Customizing the payment page theme

The configuration contains a `colors` field which specifies the theme of your payment page.

To customize the colors, add the color values in the `.env` file:

```dotenv
SELCOM_HEADER_COLOR="#FG345O"
SELCOM_LINK_COLOR="#000000"
SELCOM_BUTTON_COLOR="#E244FF"
```
