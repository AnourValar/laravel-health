# Laravel Health

## Installation

```bash
composer require anourvalar/laravel-health
```


## Checks for spatie/laravel-health

### FilesystemCheck
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\FilesystemCheck::new()->disks(['s3' => ($checkPublicUrl = true)]),
]);
```

### OpcacheCheck
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\OpcacheCheck::new(),
]);
```

### PusherCheck
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\PusherCheck::new()->connection(null), // default connection
]);
```

### QueueFailedCheck
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\QueueFailedCheck::new(),
]);
```

### XdebugCheck
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\XdebugCheck::new(),
]);
```

### SSL Certificate
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\SSLCertCheck::new()
      ->url('google.com')
      ->warnWhenExpiringDay(10)
      ->failWhenExpiringDay(2),
]);
```

### Cpu Load
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\CpuLoadCheck::new()->failWhenLoadIsHigher(
        2.5, // last minute
        2.0, // last 5 minutes
        1.5  // last 15 minutes
    ),
]);
```

### Gzip
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\GzipCheck::new()
        ->shouldBeGzipped('/')
        ->shouldNotBeGzipped('/image.png'),
]);
```

### Reverse Proxy Security

First, you need to register a route for the checker.

```php
Route::any('/health-ping', HealthPingController::class);
```

```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\ReverseProxySecurityCheck::new()->url('/health-ping'),
]);
```

### HTTP -> HTTPS 301 redirect
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\Http2HttpsCheck::new()
        ->shouldBeRedirected(['/', '/image.png']),
]);
```

### WWW -> none WWW 301 redirect
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\Www2NoneCheck::new()
        ->shouldBeRedirected(['/', '/image.png']),
]);
```

### Mailer
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\MailerCheck::new()
        ->mailer(null), // default
]);
```

### Sentry
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\SentryCheck::new(),
]);
```

### Directory Permissions
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\DirectoryPermissionsCheck::new()
        ->writable(storage_path('logs'))
        ->notWritable(app_path('')),
]);
```

### CORS
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\CorsCheck::new()
        ->allowed('https://good.com')
        ->disallowed('https://evil.com')
        ->url('api/sanctum/csrf-cookie'), // target endpoint
]);
```

### Cache Headers
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\CacheHeadersCheck::new()
        ->shouldBeCached('/image.png')
        ->shouldNotBeCached('/'),
]);
```

### Root (user)
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\RootCheck::new(),
]);
```

### FastCGI

First, you need to register a route for the checker.

```php
Route::any('/health-ping', HealthPingController::class);
```

```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\FastCGICheck::new()->url('/health-ping'),
]);
```

### Queue size
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\QueueSizeCheck::new()
        ->add(['connection' => null, 'name' => null, 'max_size' => 200]),
]);
```

### Octane server
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\OctaneCheck::new(),
]);
```

### Redis Config
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\RedisConfigCheck::new(),
]);
```

### HTTP/2
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\HttpV2Check::new()->urls('/'),
]);
```

### HSTS
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\HstsCheck::new()->urls('/'),
]);
```
