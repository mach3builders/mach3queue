# Changelog

## Unreleased

### Added
- **`before_job` hook** — A configurable callback that runs before each job is processed. Use it to reconnect stale database connections or perform other housekeeping in long-running workers. Set it in your `queue.php` config:

```php
'before_job' => function () {
    // Reconnect your app's database, clear caches, etc.
},
```

### Fixed
- **After-callback exceptions no longer crash workers** — If an `->after()` callback throws an exception during `CompleteJob` or `BuryJob`, the error is now caught and logged instead of propagating up and killing the worker process.
