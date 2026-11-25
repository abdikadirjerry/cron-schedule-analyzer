<?php

declare(strict_types=1);

namespace CronStudio;

final class ViewShell
{
    public static function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * @param array{valid: bool, error: ?string, fields: array<string, list<int>>, human: string} $parsed
     * @param list<string> $runs
     */
    public static function render(string $activeTab, string$expression, array $parsed, array$runs, int $collisionCount, string$innerHtml): string
    {
        $tabs = [ 'simulator' => 'Cron Expression Simulator', 'collisions' => 'Multi-Job Collision Auditor', 'json' => 'JSON Schedule API', ];$nav = '';
        foreach ($tabs as$slug => $label) {$cls = $slug ===$activeTab ? 'active' : '';
            $nav .= '<a href="/?tab=' . $slug . '" class="' . $cls . '">' . self::esc($label) . '</a>';
        }

        $nextRun =$runs[0] ?? 'N/A';
        $validState =$parsed['valid'] ? 'VALID 5-FIELD' : 'SYNTAX ERROR';
        $validClass =$parsed['valid'] ? 'sage' : 'crimson';

        return '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CronPulse // Stateless PHP Cron Expression &amp; Collision Studio</title>
  <link rel="stylesheet" href="/assets/scheduler.css" />
</head>
<body>
  <header class="topbar">
    <div class="brand">CRON<span>PULSE</span> // 5-FIELD CRON PARSER &amp; COLLISION ANALYZER</div>
    <nav class="nav-tabs">' . $nav . '</nav>
  </header>
  <main class="container">
    <section class="kpi-strip">
      <div class="card">
        <div class="kpi-label">Expression Status</div>
        <div class="kpi-val ' . $validClass . '">' . $validState . '</div>
      </div>
      <div class="card">
        <div class="kpi-label">Active Pattern</div>
        <div class="kpi-val terracotta">' . self::esc($expression) . '</div>
      </div>
      <div class="card">
        <div class="kpi-label">Next Execution (UTC)</div>
        <div class="kpi-val sage" style="font-size:1.05rem; margin-top:0.45rem;">' . self::esc($nextRun) . '</div>
      </div>
      <div class="card">
        <div class="kpi-label">Fleet Contention Windows</div>
        <div class="kpi-val ' . ($collisionCount > 0 ? 'sand' : 'sage') . '">' . $collisionCount . ' overlaps</div>
      </div>
    </section>
    ' . $innerHtml . '
  </main>
</body>
</html>';
    }
}
