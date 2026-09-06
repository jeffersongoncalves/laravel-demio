---
name: demio-development
description: Development guide for the Laravel Demio package - a lightweight Demio webinar REST API v1 client for events, registration, and participants
---

## When to use this skill

- Adding new Demio REST helpers to `DemioClient`
- Adjusting 401/credential detection or the `DemioAuthenticationException` contract
- Tuning the api_key / api_secret / base_url / timeout configuration
- Writing tests for Demio API interactions with `Http::fake()`

## Setup

### Requirements

- PHP 8.2+
- Laravel 11, 12, or 13
- spatie/laravel-package-tools ^1.14.0
- A Demio Api-Key and Api-Secret (https://my.demio.com/integrations/api)

### Installation

```bash
composer require jeffersongoncalves/laravel-demio
```

### Publish Config

```bash
php artisan vendor:publish --tag=demio-config
```

### Environment Variables

```env
DEMIO_API_KEY=your-api-key
DEMIO_API_SECRET=your-api-secret
DEMIO_BASE_URL=https://my.demio.com/api/v1
DEMIO_TIMEOUT=8
```

## Architecture

### Namespace Structure

```
JeffersonGoncalves\Demio\
    DemioServiceProvider          # Registers the config file only
    DemioClient                   # Static REST client
    Exceptions\
        DemioAuthenticationException  # Thrown on a 401
```

### Service Provider

The provider is intentionally minimal — it only registers the config file via
`spatie/laravel-package-tools`:

```php
public function configurePackage(Package $package): void
{
    $package
        ->name('demio')
        ->hasConfigFile();
}
```

The package short name is `demio`, so the published config lives at
`config/demio.php` and the publish tag is `demio-config`.

## Public API

```php
use JeffersonGoncalves\Demio\DemioClient;

DemioClient::ping();                                    // array<string,mixed>|null
DemioClient::events();                                  // list<array<string,mixed>>
DemioClient::events('upcoming');                        // 'upcoming'|'past'|'all'
DemioClient::event('event-id');                         // array<string,mixed>|null
DemioClient::eventDate('event-id', 'date-id');          // array<string,mixed>|null
DemioClient::register('event-id', 'Jane Doe', 'jane@example.com', dateId: 'date-id', refUrl: 'https://example.com');
DemioClient::participants('date-id');                   // list<array<string,mixed>>
```

## Authentication Detection

Detection runs inside the shared `request()` helper, **outside** the network
try/catch, so the exception propagates to the caller instead of being
swallowed and logged as a generic fetch failure.

```php
if ($response->status() === 401) {
    throw new DemioAuthenticationException;
}
```

## Configuration

```php
// config/demio.php
return [
    'api_key' => env('DEMIO_API_KEY'),
    'api_secret' => env('DEMIO_API_SECRET'),
    'base_url' => env('DEMIO_BASE_URL', 'https://my.demio.com/api/v1'),
    'timeout' => (int) env('DEMIO_TIMEOUT', 8),
];
```

Credentials, base URL, and timeout are read lazily via `config()` calls inside
the client so they can be overridden at runtime (e.g. in tests).

## Testing Patterns

### Mocking Demio responses

```php
use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Demio\DemioClient;

it('lists events', function () {
    Http::fake([
        'my.demio.com/api/v1/events*' => Http::response([
            ['id' => '1', 'name' => 'Webinar A'],
        ], 200),
    ]);

    expect(DemioClient::events())->toBe([
        ['id' => '1', 'name' => 'Webinar A'],
    ]);
});
```

### Asserting a thrown 401

```php
use JeffersonGoncalves\Demio\Exceptions\DemioAuthenticationException;

it('throws on a 401', function () {
    Http::fake([
        'my.demio.com/api/v1/ping' => Http::response('', 401),
    ]);

    expect(fn () => DemioClient::ping())->toThrow(DemioAuthenticationException::class);
});
```

## Dev Commands

```bash
# Run tests
vendor/bin/pest

# Run static analysis (PHPStan level 5 + Larastan)
vendor/bin/phpstan analyse

# Format code (Pint, Laravel preset)
vendor/bin/pint
```
