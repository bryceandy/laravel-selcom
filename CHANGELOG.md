# Change Log

## [v0.0.7](https://github.com/bryceandy/laravel-selcom/compare/v0.0.6...v0.0.7) - June 23, 2022
* Bump `guzzlehttp/guzzle` to fix change in port should be considered a change in origin

## [v0.0.6](https://github.com/bryceandy/laravel-selcom/compare/v0.0.5...v0.0.6) - June 18, 2022
* Bump `guzzlehttp/psr7` to fix cross domain cookie leakage
* Bump `guzzlehttp/psr7` to fix failure to strip authorization header on HTTP downgrade
* Bump `guzzlehttp/psr7` to fix failure to strip the cookie header on change in host or HTTP downgrade

## [v0.0.5](https://github.com/bryceandy/laravel-selcom/compare/v0.0.4...v0.0.5) - April 28, 2022
* Bump `guzzlehttp/psr7` to fix security issue Improper Input Validation

## [v0.0.4](https://github.com/bryceandy/laravel-selcom/compare/v0.0.3...v0.0.4) - December 25, 2021
* Return the payment gateway URL as data instead of redirecting to the URL for JSON requests

## [v0.0.3](https://github.com/bryceandy/laravel-selcom/compare/v0.0.2...v0.0.3) - October 28, 2021
 * Add support for PHP 8

## [v0.0.2](https://github.com/bryceandy/laravel-selcom/compare/v0.0.1...v0.0.2) - September 15, 2021
 * Add `selcom_transaction_id` to the payments table

## v0.0.1 - September 15, 2021
 * Checkout API
 * Initial release
