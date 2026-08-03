# Changelog

## Unreleased

### Fixed
- **A dead application connection no longer fails an `->after()` callback** — Workers outlive the connections their jobs use. When an `->after()` callback failed because its connection had gone away (`MySQL server has gone away`), the callback was skipped and the error only logged. The callback now gets one retry: the `before_job` callback runs to refresh the connections, then the callback is attempted again. Only lost-connection failures are retried; every other exception is logged as before.

  This closes the crash path reported from workers on 1.2.1 and earlier, where an unguarded after-callback took the whole worker process down (fixed in 1.3.0, now recovered from rather than merely survived).

### Changed
- **`before_job` also serves as the connection refresher** — Besides running before each job, the callback is now invoked when a lost connection is detected mid-callback. No config change needed.

## 1.3.0 - 2026-03-05

### Added
- **`before_job` hook** — A configurable callback that runs before each job is processed. Use it to reconnect stale database connections or perform other housekeeping in long-running workers. Set it in your `queue.php` config:

```php
'before_job' => function () {
    // Reconnect your app's database, clear caches, etc.
},
```

### Fixed
- **After-callback exceptions no longer crash workers** — If an `->after()` callback throws an exception during `CompleteJob` or `BuryJob`, the error is now caught and logged instead of propagating up and killing the worker process.
