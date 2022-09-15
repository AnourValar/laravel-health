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
