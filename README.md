# Laravel Health

## Installation

```bash
composer require anourvalar/laravel-health
```


## Checks for spatie/laravel-health

### FilesystemCheck
```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\FilesystemCheck::new()->disks(['s3']),
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

### QueueCheck

First, you must schedule dispatching of the AnourValar\LaravelHealth\Jobs\QueueCheckJob to run every minute. 

```php
$schedule->call(fn () => dispatch(new QueueCheckJob()))->name('health:check-queue')->everyMinute();
```

```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\QueueCheck::new(),
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
    )
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

First, you must create a route for the checker.

```php
Route::any('/reverse-proxy-security', ReverseProxySecurityController::class);
```

```php
\Spatie\Health\Facades\Health::checks([
    \AnourValar\LaravelHealth\ReverseProxySecurityCheck::new()->url('/reverse-proxy-security'),
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
