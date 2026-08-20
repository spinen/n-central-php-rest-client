# SPINEN's N-central PHP Client

[![CI](https://github.com/spinen/n-central-php-rest-client/actions/workflows/ci.yml/badge.svg)](https://github.com/spinen/n-central-php-rest-client/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/spinen/n-central-php-rest-client/v/stable)](https://packagist.org/packages/spinen/n-central-php-rest-client)
[![PHP Version](https://img.shields.io/packagist/php-v/spinen/n-central-php-rest-client)](https://packagist.org/packages/spinen/n-central-php-rest-client)
[![License](https://img.shields.io/github/license/spinen/n-central-php-rest-client)](LICENSE)

PHP package to interface with [N-able's N-central Server](https://www.n-able.com/products/n-central-rmm). We strongly encourage you to review N-central's API docs to get a feel for what this package can do, as we are just wrapping their API. We have based the majority of this code from our [Halo PHP Client](https://github.com/spinen/halo-php-client).

We solely use [Laravel](https://www.laravel.com) for our applications, so this package is written with Laravel in mind. We have tried to make it work outside of Laravel. If there is a request from the community to split this package into 2 parts, then we will consider doing that work.

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

* `delete(string $path)` - Shortcut to the `request()` method with 'DELETE' as the last parameter

* `get(string $path)` - Shortcut to the `request()` method with 'GET' as the last parameter

* `getToken()` - Get, return, or refresh the token.

> NOTE: This is the best way to get a token as it handles expiration

* `post(string $path, array $data)` - Shortcut to the `request()` method with 'POST' as the last parameter

* _[NOT YET SUPPORTED BY API]_ ~~`put(string $path, array $data)` - Shortcut to the `request()` method with 'PUT' as the last parameter~~

* `refreshToken()` - Refresh a token

* `request(?string $path, ?array $data = [], ?string $method = 'GET')` - Make an API call to N-central to `$path` with the `$data` using the JWT for the logged in user.

* `requestToken()` - Request a token

* `setDebug(bool $debug)` - Set Guzzle to debug

* `setToken(Token|string $token)` - Set the token for the N-central API

* `uri(?string $path = null, ?string $url = null)` - Generate a full uri for the path to the N-central API.

* `validToken()` - Is the token valid

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

Some of the models have relationships to other models. You can call the relationship as a method and the additional API calls are automatically made & returned.

```php
> $customer = $builder->customers->first()
= Spinen\Ncentral\Customer {#4967}

// Get all devices for this customer
> $customer->devices()->get()
= Spinen\Ncentral\Support\Collection {#5001
    all: [
      Spinen\Ncentral\Device {#5003},
      // more...
    ],
  }

// Get the service organization that owns this customer
> $customer->serviceOrganization
= Spinen\Ncentral\ServiceOrganization {#5010}
```

**Available relationships:**

| Model | Relationship | Returns |
|-------|--------------|---------|
| ServiceOrganization | `customers()` | Collection of Customer |
| ServiceOrganization | `devices()` | Collection of Device |
| Customer | `serviceOrganization()` | ServiceOrganization |
| Customer | `sites()` | Collection of Site |
| Customer | `devices()` | Collection of Device |
| Customer | `softwareInstallers()` | Collection of SoftwareInstaller |
| Site | `customer()` | Customer |
| Site | `devices()` | Collection of Device |
| Device | `customer()` | Customer |
| Device | `customProperties()` | Collection of DeviceCustomProperty |
| Device | `notes()` | Collection of DeviceNote |
| Device | `asset()` | DeviceAsset |
| Device | `lifecycle()` | DeviceLifecycle |
| Device | `maintenanceWindows()` | Collection of MaintenanceWindow |
| Device | `serviceMonitorStatus()` | Collection of ServiceMonitorStatus |
| Device | `remoteControl()` | RemoteControl |
| Device | `tasks()` | Collection of DeviceTask |
| Device | `activationKey()` | string |
| ScheduledTask | `device()` | Device |
| ScheduledTask | `details` | DetailedScheduledTask |

**OrgUnit relationships** (inherited by ServiceOrganization, Customer, Site):

| Relationship | Returns |
|--------------|---------|
| `customProperties()` | Collection of OrgUnitCustomProperty |
| `activeIssues()` | Collection of ActiveIssue |
| `jobStatuses()` | Collection of JobStatus |
| `userRoles()` | Collection of UserRole |
| `limits()` | Collection of OrgUnitLimit |
| `users()` | Collection of User |
| `accessGroups()` | Collection of AccessGroup |
| `registrationToken()` | string |
| `customPropertyDefaults()` | Collection |

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

$builder->customers->pluck('customerName', 'customerId')->sort()
= Spinen\Ncentral\Support\Collection {#4959
    all: [
      18 => "Customer A",
      17 => "Customer B",
    ],
  }
```

## Available Models

The following models are available through the builder:

| Property | Model | Description |
|----------|-------|-------------|
| `accessGroups` | `AccessGroup` | Access groups |
| `applianceTasks` | `ApplianceTask` | Appliance task information |
| `customers` | `Customer` | Customer organizations |
| `customPsaTickets` | `CustomPsaTicket` | Custom PSA tickets |
| `detailedScheduledTasks` | `DetailedScheduledTask` | Detailed scheduled task information |
| `deviceFilters` | `DeviceFilter` | Device filters |
| `devices` | `Device` | Managed devices |
| `deviceTasks` | `DeviceTask` | Tasks assigned to devices |
| `health` | `Health` | System health status |
| `reports` | `Report` | Reports |
| `scheduledTasks` | `ScheduledTask` | Scheduled tasks |
| `serverInfo` | `ServerInfo` | N-central server information |
| `serviceOrganizations` | `ServiceOrganization` | Service organizations |
| `sites` | `Site` | Customer sites |
| `users` | `User` | N-central users |

## Known Issues

* The N-central API is under active development and endpoints may change
* User-Agent header contains `/` character which may cause issues with strict HTTP parsers

## Known Limitations

| Location | Issue |
|----------|-------|
| `Client.php:68` | Token refresh validation relies on API behavior |
| `Client.php:124` | PUT method disabled until N-central adds supporting endpoints |
| `Token.php:9` | Token expiry buffer (5 min) may need tuning for specific environments |
| `ScheduledTask.php:32` | Default credential values (LocalSystem) may need adjustment per use case |

## Not Implemented

The following N-central API endpoints are not yet supported:

* `StandardPsa` integration (`/api/standard-psa/*`) - Complex multi-endpoint PSA integration
