# Petfinder PHP SDK

[![CircleCI](https://circleci.com/gh/petfinder-com/petfinder-php-sdk.svg?style=shield)](https://circleci.com/gh/petfinder-com/petfinder-php-sdk)
[![packagist version](https://img.shields.io/packagist/v/petfinder-com/petfinder-php.svg)](https://packagist.org/packages/petfinder-com/petfinder-php)
[![Coverage Status](https://coveralls.io/repos/github/petfinder-com/petfinder-php-sdk/badge.svg?branch=feature%2Fcoveralls)](https://coveralls.io/github/petfinder-com/petfinder-php-sdk?branch=feature%2Fcoveralls)

A simple wrapper for the Petfinder API, written in PHP.

Uses [Petfinder API v2](https://www.petfinder.com/developers/v2/docs/).

## Features

* Uses HTTPlug
* Supports Async requests
* Well tested

## Requirements

* PHP >= 7.1
* A [HTTPlug Async client](https://packagist.org/providers/php-http/async-client-implementation)
* A [PSR-7 implementation](https://packagist.org/providers/psr/http-message-implementation)

## Install

In addition to the Petfinder package, you'll need an [HTTPlug](http://docs.php-http.org/en/latest/httplug/users.html)
client that support async requests. We recommend using `php-http/guzzle6-adapter`,
but you are free to use whatever one works for you.

    composer require petfinder-com/petfinder-php php-http/guzzle6-adapter

## Usage

Basic usage

```php
$client = new \Petfinder\Client('my-api-key', 'my-api-secret');

$client->animal->search(['type' => 'Dog']);
```

Using async requests

```php
$client = new \Petfinder\Client('my-api-key', 'my-api-secret');

$client->organization->searchAsync()->then(function (\Petfinder\Result $result) {
    // Do something with $result
})->catch(function (\Petfinder\Exception\ProblemDetailsException $exception) {
    // Do something with $exception
});
```

Using a custom Httplug client

```php
$builder = new \Petfinder\Http\Builder($myHttpClient);
$client = new \Petfinder\Client('my-api-key', 'my-api-secret', $builder);
```
