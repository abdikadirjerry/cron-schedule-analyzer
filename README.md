# CronPulse — Stateless PHP 8 Cron Expression & Collision Analyzer (`php-cron-schedule-analyzer`)

A zero-dependency, database-free **5-Field Cron Expression Parser, Next-Execution Timeline Simulator, and Multi-Job Collision Auditor** built with pure PHP 8.

## Features
- **Strict 5-Field Expansion (`src/CronParser.php`):** Parses wildcards (`*`), steps (`*/15`), ranges (`9-17`), and comma lists with bounds validation.
- **Next-Run Simulator & Collision Auditor (`src/ScheduleSimulator.php`):** Projects upcoming UTC timestamps and detects overlapping cron job spikes across multi-job manifests.
- **Stateless JSON API (`GET /api/schedule`):** Returns parsed field arrays, upcoming runs, and collision windows with zero database or Composer dependencies.

## Running Locally
```bash
php -S 127.0.0.1:8089 -t public
```
Open `http://127.0.0.1:8089` in your browser, or run the CLI test suite:
```bash
php tests/run_all.php
```
