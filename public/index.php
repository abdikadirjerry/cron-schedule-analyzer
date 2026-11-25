<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/autoload.php';

use CronStudio\CronParser;
use CronStudio\ScheduleSimulator;
use CronStudio\ViewShell;
use CronStudio\ViewTabs;

$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($uriPath === '/assets/scheduler.css') {
    header('Content-Type: text/css; charset=utf-8');
    readfile(__DIR__ . '/assets/scheduler.css');
    exit;
}

$defaultExpr = '*/15 8-18 * * 1-5';$defaultManifest = "db-snapshot = 0 * * * *\nbilling-sync = */15 * * * *\nreport-digest = 0 9,12,18 * * 1-5";

$tab = (string) ($_GET['tab'] ?? 'simulator');$expression = trim((string) ($_POST['expression'] ?? ($_GET['expr'] ?? $defaultExpr)));$manifestText = trim((string) ($_POST['manifest'] ?? $defaultManifest));

$jobs = [];
foreach (explode("\n", $manifestText) as$line) {
    if (str_contains($line, '=')) {
        [$name,$expr] = explode('=', $line, 2);$jobs[trim($name)] = trim($expr);
    }
}

$parsed = CronParser::parse($expression);
$runs = ScheduleSimulator::nextRuns($expression, 10);
$audit = ScheduleSimulator::detectCollisions($jobs);

$jsonPayload = (string) json_encode([
    'expression' => $expression,
    'parsed' => $parsed,
    'next_runs_utc' => $runs,
    'collisions' => $audit['collisions'],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if ($uriPath === '/api/schedule') {
    header('Content-Type: application/json; charset=utf-8');
    echo $jsonPayload;
    exit;
}

if ($tab === 'collisions') {$body = ViewTabs::renderCollisions($manifestText,$audit);
} elseif ($tab === 'json') {
    $body = ViewTabs::renderJsonTab($jsonPayload);
} else {
    $tab = 'simulator';
    $body = ViewTabs::renderSimulator($expression, $parsed,$runs);
}

header('Content-Type: text/html; charset=utf-8');
echo ViewShell::render($tab,$expression, $parsed,$runs, count($audit['collisions']),$body);
