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

Then proceed to install

```bash
composer require bryceandy/laravel-selcom
```

## Configuration

To access Selcom's APIs, you will need to provide the package with access to your Selcom vendorID, API Key and Secret Key.

For this we need to publish the package's configuration file using:

```bash
php artisan vendor:publish --tag=selcom-config
```

After obtaining the three credentials from Selcom support, add their values in the `.env` variables

```dotenv
SELCOM_VENDOR=123456
SELCOM_API_KEY=yourApiKey
SELCOMM_SECRET=yourSecretKey

SELCOM_IS_LIVE=false
```

Note that when starting you will be provided with test credentials and this is why `SELCOM_IS_LIVE` is initially false.

When you change to live credentials don't forget to change `SELCOM_IS_LIVE` to true.

