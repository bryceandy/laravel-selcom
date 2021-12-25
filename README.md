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

Run the migration command to create a table that stores Selcom payments:

```
php artisan migrate
```

## Checkout API

Checkout is the simplest Selcom API we can start processing payments with.

### Checkout payments using USSD

This API automatically pulls your user's USSD payment menu directly after being called.

**Note**: As of now, this is only applicable to AirtelMoney and TigoPesa customers.

```php
use Bryceandy\Selcom\Facades\Selcom;

Selcom::checkout([
    'name' => "Buyer's full name", 
    'email' => "Buyer's email",
    'phone' => "Buyer's msisdn, for example 255756334000",
    'amount' => "Amount to be paid",
    'transaction_id' => "Unique transaction id",
    'no_redirection' => true,
    // Optional fields
    'currency' => 'Default is TZS',
    'items' => 'Number of items purchased, default is 1',
    'payment_phone' => 'The number that will make the USSD transactions, if not specified it will use the phone value',
]);
```

Other networks may use USSD checkout manually with tokens as shown with other checkout options below.

### Checkout to the payments page (without cards)

The payment page contains payment options such as QR code, Masterpass, USSD wallet pull, mobile money payment with tokens.

To redirect to this page, we will use the previous example, but **return** without the `no_redirection` option or assign it to `false`:

```php
use Bryceandy\Selcom\Facades\Selcom;

return Selcom::checkout([
    'name' => "Buyer's full name", 
    'email' => "Buyer's email",
    'phone' => "Buyer's msisdn, for example 255756334000",
    'amount' => "Amount to be paid",
    'transaction_id' => "Unique transaction id",
]);
```

### Checkout to the payments page (with cards)

To use the cards on the payment page, return the following request:

```php
use Bryceandy\Selcom\Facades\Selcom;

return Selcom::cardCheckout([
    'name' => "Buyer's full name", 
    'email' => "Buyer's email",
    'phone' => "Buyer's msisdn, for example 255756334000",
    'amount' => "Amount to be paid",
    'transaction_id' => "Unique transaction id",
    'address' => "Your buyer's address",
    'postcode' => "Your buyer's postcode",
    // Optional fields
    'user_id' => "Buyer's user ID in your system",
    'buyer_uuid' => $buyerUuid, // Important if the user has to see their saved cards.
    // See the last checkout section on how to fetch a buyer's UUID
    'country_code' => "Your buyer's ISO country code: Default is TZ",
    'state' => "Your buyer's state: Default is Dar Es Salaam",
    'city' => "Your buyer's city: Default is Dar Es Salaam",
    'billing_phone' => "Your buyer's billing phone number: forexample 255756334000",
    'currency' => 'Default is TZS',
    'items' => 'Number of items purchased, default is 1',
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

<p align="center">
<img src="https://bryceandy-devblog.s3-us-east-2.amazonaws.com/1631711505.png" width="400" style="margin-left:20px;">
<img src="https://bryceandy-devblog.s3-us-east-2.amazonaws.com/1631711678.png" width="400">
</p>

- Also, you can assign a prefix for the package. This will be applied to the routes and order IDs

```dotenv
SELCOM_PREFIX=SHOP
```

#### Customizing the payment page theme

The configuration contains a `colors` field which specifies the theme of your payment page.

To customize the colors, add the color values in the `.env` file:

```dotenv
SELCOM_HEADER_COLOR="#FG345O"
SELCOM_LINK_COLOR="#000000"
SELCOM_BUTTON_COLOR="#E244FF"
```

For JSON requests (API applications), this type of checkout to the payments page will return data with
`payment_gateway_url` instead of redirecting to that page:

```json
{
  "payment_gateway_url": "https://example.selcommobile-url.com"
}
```

### Checkout payments with cards (without navigating to the payment page)

To use a card without navigating to the payment page, you need to have already created a card for the paying user by navigating to the payment page.

This is very useful for recurring or on-demand card payments. The data is the same as the previous card checkout, except we are adding `no_redirection`, `user_id` & `buyer_uuid`:

```php
use Bryceandy\Selcom\Facades\Selcom;

Selcom::cardCheckout([
    'name' => "Buyer's full name", 
    'email' => "Buyer's email",
    'phone' => "Buyer's msisdn, for example 255756334000",
    'amount' => "Amount to be paid",
    'transaction_id' => "Unique transaction id",
    'no_redirection' => true,
    'user_id' => "Buyer's user ID in your system",
    'buyer_uuid' => $buyerUuid, // See instructions below on how to obtain this value
    'address' => "Your buyer's address",
    'postcode' => "Your buyer's postcode",
    // Optional fields
    'country_code' => "Your buyer's ISO country code: Default is TZ",
    'state' => "Your buyer's state: Default is Dar Es Salaam",
    'city' => "Your buyer's city: Default is Dar Es Salaam",
    'billing_phone' => "Your buyer's billing phone number: forexample 255756334000",
    'currency' => 'Default is TZS',
    'items' => 'Number of items purchased, default is 1',
]);
```

This method will fetch 3 saved cards of the user and try all of them until a payment is successful or all fail.

#### Obtaining the buyer's UUID

If this user has visited the payment page before to make a payment, then their uuid is already in the database.

```php
use Illuminate\Support\Facades\DB;

$buyerUuid = DB::table('selcom_payments')
    ->where([
        ['user_id', '=' auth()->id()],
        ['gateway_buyer_uuid', '<>', null],
    ])
    ->value('gateway_buyer_uuid');
```

### Listing a user's stored cards

To fetch the user's stored cards could be useful to know if a user has cards, or if there is a need to delete.

You will require a user's ID and `buyer_uuid`:

```php
use Bryceandy\Selcom\Facades\Selcom;

Selcom::fetchCards($userId, $gatewayBuyerUuid);
```

### Deleting a user's stored card

To delete a user's stored card you need a `buyer_uuid` and card ID obtained from `fetchCards` request above.

```php
use Bryceandy\Selcom\Facades\Selcom;

Selcom::deleteCard($cardId, $gatewayBuyerUuid);
```

### Checkout webhook/callback

The package comes with an implementation of the payment webhook. 

When Selcom sends the payment status to your site, the data in the `selcom_payments` table will be updated and an event `Bryceandy\Selcom\Events\CheckoutWebhookReceived` will be dispatched.

You can create a listener for the event:

```php
class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \Bryceandy\Selcom\Events\CheckoutWebhookReceived::class => [
            \App\Listeners\ProcessWebhook::class,
        ],
    ];
}
```

Then in your listener `App\Listeners\ProcessWebhook`, you can do anything with the order ID:

```php
<?php

namespace App\Listeners;

use Bryceandy\Selcom\Events\CheckoutWebhookReceived;
use Bryceandy\Selcom\Facades\Selcom;
use Illuminate\Support\Facades\DB;

class ProcessWebhook
{
    /**
     * Handle the event.
     *
     * @param CheckoutWebhookReceived $event
     */
    public function handle(CheckoutWebhookReceived $event)
    {
        // Get the order id
        $orderId = $event->orderId;
        
        // Fetch updated record in the database, and do what you need with it
        $status = DB::table('selcom_payments')
            ->where('order_id', $orderId)
            ->value('payment_status');
        
        if ($status === 'PENDING') {
            Selcom::orderStatus($orderId); // Or dispatch a job minutes later to query order status
        }
    }
}
```

### Check order status

To query order statuses to Selcom, simply run:

```php
use Bryceandy\Selcom\Facades\Selcom;
use Illuminate\Support\Facades\DB;

$order = Selcom::orderStatus($orderId);
```

Once you have obtained the order data, you can use it as you wish. The example below updates the payment in the database:

```php
DB::table('selcom_payments')->where('order_id', $orderId)
    ->update(array_merge(
        'payment_status' => $order['payment_status'],
        ($order['payment_status'] === 'COMPLETED'
            ? [
                'selcom_transaction_id' => $order['transid'],
                'channel' => $order['channel'],
                'reference' => $order['reference'],
                'msisdn' => $order['msisdn'],
            ]
            : []
        )
    ));
```

### List orders

To list all orders made to Selcom, simply indicate `from_date` and `to_date`:

```php
use Bryceandy\Selcom\Facades\Selcom;

$fromDate = '2021-02-16';
$toDate = '2021-12-25';

Selcom::listOrders($fromDate, $toDate);
```

### Cancel order

To cancel a Selcom order, simply run:

```php
use Bryceandy\Selcom\Facades\Selcom;

Selcom::cancelOrder($orderId);
```