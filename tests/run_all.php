<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/autoload.php';

use CronStudio\CronParser;
use CronStudio\ScheduleSimulator;

// 1. Step and range expansion check
$parsed = CronParser::parse('*/20 9-11 1,15 * 1-5');
if (!$parsed['valid'] or $parsed['fields']['minute'] !== [0, 20, 40] or$parsed['fields']['hour'] !== [9, 10, 11]) {
    throw new RuntimeException('Expected valid expansion for */20 9-11');
}

// 2. Invalid out-of-bounds field check
$bad = CronParser::parse('75 * * * *');
if ($bad['valid']) {
    throw new RuntimeException('Expected minute 75 to fail validation');
}

// 3. Deterministic next-run & collision detection check
$anchor = new DateTimeImmutable('2026-11-25 09:50:00', new DateTimeZone('UTC'));
$runs = ScheduleSimulator::nextRuns('0 * * * *', 2,$anchor);
if ($runs !== ['2026-11-25 10:00 UTC', '2026-11-25 11:00 UTC']) {
    throw new RuntimeException('Unexpected nextRuns output: ' . implode(', ', $runs));
}

$audit = ScheduleSimulator::detectCollisions([ 'hourly-backup' => '0 * * * *', 'quarter-hourly' => '*/15 * * * *', ],$anchor);

if (!isset($audit['collisions']['2026-11-25 10:00 UTC'])) {
    throw new RuntimeException('Expected collision at 2026-11-25 10:00 UTC');
}

echo "[PASS] All CronPulse PHP 8 unit assertions passed cleanly.\n";
