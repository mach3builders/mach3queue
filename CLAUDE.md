# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Mach3Queue is a PHP job queue system with a Vue 3 dashboard. It provides supervisor/worker process management for distributed job processing, backed by a database (Illuminate Database). The frontend dashboard monitors queue status using Tailwind CSS and Vite.

## Commands

### PHP Tests (Pest)
```bash
./vendor/bin/pest                              # Run all tests
./vendor/bin/pest tests/Feature/QueueTest.php  # Run specific test file
./vendor/bin/pest --filter="test name"         # Run tests matching pattern
./vendor/bin/pest --bail                       # Stop on first failure
```

Tests use an in-memory SQLite database, configured automatically in `tests/Pest.php`.

### Frontend (Vue/Vite)
```bash
npm run dev      # Vite dev server
npm run build    # Production build
```

### Queue CLI
```bash
./vendor/bin/queue publish     # Publish config file
./vendor/bin/queue install     # Setup database tables
./vendor/bin/queue start       # Start queue system
./vendor/bin/queue restart     # Graceful restart
./vendor/bin/queue terminate   # Graceful stop
```

## Architecture

### Supervisor Hierarchy
`MasterSupervisor` → `Supervisor` (multiple) → `WorkerProcess` (multiple per supervisor)

The MasterSupervisor monitors supervisors, each supervisor manages a pool of worker processes via `ProcessPool`. The `AutoScaler` scales workers up/down based on workload thresholds defined in config.

### Job Lifecycle
Jobs flow through statuses defined in `src/Job/Status.php`: PENDING → PROCESSING → COMPLETED or FAILED (buried).

Action classes in `src/Action/` handle each step: `AddJob` → `ReserveJob` → `RunJob` → `CompleteJob` or `BuryJob`. `TrimOldJobs` removes expired completed/failed jobs based on config retention times.

### Key Entry Points
- `queue` (root) — CLI executable that loads config and routes to `src/Console/Console.php`
- `src/Queue/QueueManager.php` — Static facade; call `QueueManager::push()` to enqueue jobs
- `src/main.js` — Vue dashboard entry point

### Dashboard
`src/Dashboard/Dashboard.php` generates the monitoring UI. `DashboardData.php` gathers job stats, `DashboardHtml.php` renders the page. The Vue app in `src/components/` communicates via `fetch.js`.

### Configuration
`config/queue.php` defines bootstrap path, job trim retention, and supervisor settings (max/min processes, timeout, memory limit, balance cooldown, max workload, max retries, retry delay).

### Process Control
Uses `ext-pcntl` and `ext-posix` for Unix signal handling. `ListensForSignals` trait provides signal listening. Workers and supervisors respond to SIGTERM/SIGINT for graceful shutdown.
