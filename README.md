# SPINEN's N-central PHP Client

[![Latest Stable Version](https://poser.pugx.org/spinen/n-central-php-rest-client/v/stable)](https://packagist.org/packages/spinen/n-central-php-rest-client)
[![Latest Unstable Version](https://poser.pugx.org/spinen/n-central-php-rest-client/v/unstable)](https://packagist.org/packages/spinen/n-central-php-rest-client)
[![Total Downloads](https://poser.pugx.org/spinen/n-central-php-rest-client/downloads)](https://packagist.org/packages/spinen/n-central-php-rest-client)
[![License](https://poser.pugx.org/spinen/n-central-php-rest-client/license)](https://packagist.org/packages/spinen/n-central-php-rest-client)

PHP package to interface with [N-able's N-central Server](https://www.n-able.com/products/n-central-rmm). We strongly encourage you to review N-central's API docs to get a feel for what this package can do, as we are just wrapping their API.  We have based the majority of this code from our [Halo PHP Client](https://github.com/spinen/halo-php-client).

We solely use [Laravel](https://www.laravel.com) for our applications, so this package is written with Laravel in mind. We have tried to make it work outside of Laravel. If there is a request from the community to split this package into 2 parts, then we will consider doing that work.

## Build Status

| Branch | Status | Coverage | Code Quality |
| ------ | :----: | :------: | :----------: |
| Develop | [![Build Status](https://github.com/spinen/n-central-php-rest-client/workflows/CI/badge.svg?branch=develop)](https://github.com/spinen/n-central-php-rest-client/workflows/CI/badge.svg?branch=develop) | [![Code Coverage](https://scrutinizer-ci.com/g/spinen/n-central-php-rest-client/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/spinen/n-central-php-rest-client/?branch=develop) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spinen/n-central-php-rest-client/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/spinen/n-central-php-rest-client/?branch=develop) |
| Master | [![Build Status](https://github.com/spinen/n-central-php-rest-client/workflows/CI/badge.svg?branch=master)](https://github.com/spinen/n-central-php-rest-client/workflows/CI/badge.svg?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/spinen/n-central-php-rest-client/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/spinen/n-central-php-rest-client/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spinen/n-central-php-rest-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/spinen/n-central-php-rest-client/?branch=master) |

## Table of Contents
 * [Installation](#installation)
 * [Laravel Setup](#laravel-setup)
    * [Configuration](#configuration)
 * [Generic PHP Setup](#generic-php-setup)
    * [Examples](#examples)
 * [Authentication](#authentication)
    * [JWT](#jwt)
 * [Usage](#usage)
    * [Supported Actions](#supported-actions)
    * [Using the Client](#using-the-client)
        * [Getting the Client object](#getting-the-client-object)
        * [Models](#models)
        * [Relationships](#relationships)
        * [Collections](#collections)
        * [Filtering using "where"](#filtering-using-where)
        * [Search](#search)
        * [Limit records returned](#limit-records-returned)
        * [Order By](#order-by)
        * [Pagination](#pagination)
    * [More Examples](#more-examples)
 * [Known Issues](#known-issues)

## Installation

Install N-central PHP Package via Composer:

```bash
$ composer require spinen/n-central-php-rest-client
```

## Laravel Setup

1. Add the appropriate values to your ```.env``` file

    #### Keys
    ```bash
    NCENTRAL_ACCESS_OVERRIDE=<Optional Override Access Token Expiration>
    NCENTRAL_JWT=<Administration → User Management → Users → Click on user → API Access → GENERATE JSON WEB TOKEN>
    NCENTRAL_REFRESH_OVERRIDE=<Optional Override Refresh Token Expiration>
    NCENTRAL_URL=<Server URL i.e. https://some.domain.tld/api/>
    ```

2. _[Optional]_ If you would like to use the client with API calls per user in your application, you will need to make your `User` object implement includes the `Spinen\Ncentral\Concerns\HasNcentral` trait which will allow it to access the Client as an attribute like this: `$user->ncentral`

    ```php
    <?php

    namespace App;

    use Illuminate\Contracts\Auth\MustVerifyEmail;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    use Spinen\Ncentral\Concerns\HasNcentral;

    class User extends Authenticatable
    {
        use HasNcentral, Notifiable;

        // ...
    }
    ```

3. _[Optional]_ Publish config & migration

    #### Config
    A configuration file named `ncentral.php` can be published to `config/` by running...

    ```bash
    php artisan vendor:publish --tag=ncentral-config
    ```

    #### Migration
    Migrations files can be published by running...

    ```bash
    php artisan vendor:publish --tag=ncentral-migrations
    ```

    You'll need the migration to set the N-central API token on your `User` model.

## Generic PHP Setup

1. You need to build up an array of configs to pass into the N-central object.  You review the `ncentral.php` file in the `configs` directory.  All of the properties are documented in the file.

2. Depending on your needs, you can either work with the N-central client or the Builder

    #### To get a `Spinen\Ncentral\Api\Client` instance for Client Credentials...

    ```bash
    $ psysh
    Psy Shell v0.11.22 (PHP 8.2.12 — cli) by Justin Hileman
    > $configs = [
        "jwt" => "sometoken",
        "url" => "https://some.host.tld/api",
    ]

    > $ncentral = new Spinen\Ncentral\Api\Client(configs: $configs);
    = Spinen\Ncentral\Api\Client {#2744}
    ```

    ####  To get a `Spinen\Ncentral\Support\Builder` instance...

    ```bash
    $ psysh
    Psy Shell v0.11.22 (PHP 8.2.12 — cli) by Justin Hileman
    > // Get a $ncentral instance from above

    > $builder = (new Spinen\Ncentral\Support\Builder)->setClient($ncentral);
    = Spinen\Ncentral\Support\Builder {#2757}

    >
    ```

    If using the `ncentral` property from the `user` model, it the will work exactly like all of the examples below where `$builder` is used.

## Authentication

N-central uses a JWT token for a user that is limited to only API calls.  This prevents ths account from being able to log directly into the application.  To obtain the "N-central User-API Token (JWT)", visit the N-central UI. Then navigate to Administration → User Management → Users → Click on user → API Access → GENERATE JSON WEB TOKEN.

## Usage

### Supported Actions for `Spinen\Ncentral\Api\Client`

* _[NOT YET SUPPORTED BY API]_ ~~`delete(string $path)` - Shortcut to the `request()` method with 'DELETE' as the last parameter~~

* `get(string $path)` - Shortcut to the `request()` method with 'GET' as the last parameter

* `getToken()` - Get, return, or refresh the token.

> NOTE: This is the best way to get a token as it handles expiration

* `post(string $path, array $data)` - Shortcut to the `request()` method with 'POST' as the last parameter

* _[NOT YET SUPPORTED BY API]_ ~~`put(string $path, array $data)` - Shortcut to the `request()` method with 'PUT' as the last parameter~~

* `refreshToken()` - Refresh a token

* `request(?string $path, ?array $data = [], ?string $method = 'GET')` - Make an [API call to N-central](https://ncentralservicedesk.com/apidoc/info) to `$path` with the `$data` using the JWT for the logged in user.

* `requestToken()` - Request a token

* `setConfigs(array $configs)` - Validate & set the configs

* `setDebug(bool $debug)` - Set Guzzle to debug

* `setToken(Token|string $token)` - Set the token for the N-central API

* `uri(?string $path = null, ?string $url = null)` - Generate a full uri for the path to the N-central API.

* `validToken()` - Is the token valid & if provided a scope, is the token approved for the scope

### Using the Client

The Client is meant to emulate [Laravel's models with Eloquent](https://laravel.com/docs/master/eloquent#retrieving-models). When working with N-central resources, you can access properties and relationships [just like you would in Laravel](https://laravel.com/docs/master/eloquent-relationships#querying-relations).

#### Models

The API responses are cast into models with the properties cast into the types as defined in the [N-central API documentation](https://ncentralservicedesk.com/apidoc/info).  You can review the models in the `src/` folder.  There is a property named `casts` on each model that instructs the Client on how to cast the properties from the API response.  If the `casts` property is empty, then the properties are not defined in the API docs, so an array is returned.

> NOTE: The documented properties on the models are likely to get stale as N-central is in active development

```php
> $builder->customers->first()
= Spinen\Ncentral\Customer {#4967
    // properties
  }

> $builder->customers->first()->toArray()
= [
    "customerId" => 249,
    "customerName" => "Customer1",
    "isSystem" => true,
    "isServiceOrg" => true,
    "parentId" => 248,
    "city" => null,
    "stateProv" => null,
    "county" => null,
    "postalCode" => "",
    "contactEmail" => null,
  ]
```

#### Relationships

> NOTE: Not yet setup

Some of the responses have links to the related resources.  If a property has a relationship, you can call it as a method and the additional calls are automatically made & returned.  The value is stored in place of the original data, so once it is loaded it is cached.

```php

```

You may also call these relationships as attributes, and the Client will return a `Collection` for you (just like Eloquent).

```php

```

#### Collections

Results are wrapped in a `Spinen\Ncentral\Support\Collection`, which extends `Illuminate\Support\Collection`, so you can use any of the collection helper methods documented  [Laravel Collection methods](https://laravel.com/docs/master/collections).

#### Filtering using "where"

You can do filters by using `where` on the models.  The first parameter is the property being filtered.  The second is optional, and is the value to filter the property.  If it is left null, then is it true, so it becomes `where('<property', true)`.  All of these values are passed in the query string.

There are a few "helper" methods that are aliases to the `where` filter, to make the calls more expressive.

* `whereId('<id>')` is an alias to `where('id', '<id>')`
* `whereNot('<property>')` is an alias to `where('<property', false)`

#### Limit records returned

You can call the `take` or `limit` methods (take is an alias to limit) on the builder to limit the records returned to the count parameter.

```php
> $customers = $builder->customers()->take(7)->get()
= Spinen\Ncentral\Support\Collection {#4999
    all: [
      Spinen\Ncentral\Customer {#4991
        // properties
      },
      // more...
    ],
  }

> $customers->count()
= 7
```

#### Pagination

Several of the endpoints support pagination.  You can use simple pagination by chaining `pagination` with an optional size value to the builder.  You can get a specific page with the `page` method that takes page number as a parameter.  You can condense the call by passing pagination size as the second parameter to the `page` method.

```php
// Could have been $builder->devices()->paginate(2)->page(2)->get()
> $devices = $builder->devices()->page(3, 2)->get()
= Spinen\Ncentral\Support\Collection {#4761
    all: [
      Spinen\Ncentral\Device {#4763
        // properties
      },
      // more...
    ],
  }

> $devices->count()
= 2
```

### More Examples

```php
> $builder->customers->count()
= 4

$builder->statuses->pluck('customerName', 'customerId')->sort()
= Spinen\Ncentral\Support\Collection {#4959
    all: [
      18 => "Customer A",
      17 => "Customer B",
    ],
  }
```

## Open Items

* Setup the relationships in the models
* Add getters to models
* Add scopes on models

## Known Issues

* They are refining the API, so things may break
