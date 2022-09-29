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

You must schedule dispatching of the AnourValar\LaravelHealth\Jobs\QueueCheckJob to run every minute. 

```php
$schedule->call(fn () => dispatch(new QueueCheckJob()))->everyMinute();
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
