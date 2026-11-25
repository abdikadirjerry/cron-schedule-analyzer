<?php

declare(strict_types=1);

namespace CronStudio;

final class ViewTabs
{
    /**
     * @param array{valid: bool, error: ?string, fields: array<string, list<int>>, human: string} $parsed
     * @param list<string> $runs
     */
    public static function renderSimulator(string $expression, array $parsed, array$runs): string
    {
        $fieldCards = '';
        if ($parsed['valid']) {
            foreach ($parsed['fields'] as$fieldName => $vals) {$chips = implode('', array_map(
                    static fn (int $v): string => '<span class="chip">' . $v . '</span>',
                    array_slice($vals, 0, 16)
                ));
                if (count($vals) > 16) {$chips .= '<span class="chip">+' . (count($vals) - 16) . ' more</span>';
                }
                $fieldCards .= '<div style="margin-bottom:0.85rem;">'
                    . '<div class="kpi-label">' . ViewShell::esc($fieldName) . ' (' . count($vals) . ' active values)</div>'
                    . '<div class="chip-row">' . $chips . '</div>'
                    . '</div>';
            }
        } else {
            $fieldCards = '<div style="color:#ef4444; font-weight:600;">' . ViewShell::esc((string) $parsed['error']) . '</div>';
        }

        $runRows = '';
        foreach ($runs as$idx => $ts) {$runRows .= '<div class="run-item"><span>Run #' . ($idx + 1) . '</span><span class="sage">' . ViewShell::esc($ts) . '</span></div>';
        }

        return '<div class="split-grid">
          <section class="card">
            <h2 style="font-size:1rem; margin-bottom:0.85rem;">Evaluate 5-Field Cron Expression</h2>
            <form class="cron-form" method="POST" action="/?tab=simulator">
              <label>
                Cron Schedule (minute hour dom month dow)
                <input type="text" name="expression" value="' . ViewShell::esc($expression) . '" required />
              </label>
              <button type="submit" class="btn-primary">Compute Next 10 Execution Times</button>
            </form>
            <div style="margin-top:1.15rem; padding-top:1rem; border-top:1px solid #362e29;">
              <div class="kpi-label" style="margin-bottom:0.35rem;">Human Summary</div>
              <div style="font-size:0.85rem; color:#f2cc8f; margin-bottom:1rem;">' . ViewShell::esc($parsed['human']) . '</div>
              ' . $fieldCards . '
            </div>
          </section>

          <section class="card">
            <h2 style="font-size:1rem; margin-bottom:0.85rem;">Upcoming Execution Timeline (UTC)</h2>
            ' . ($runRows !== '' ?$runRows : '<div style="color:#a89f96;">Fix syntax error to preview schedule.</div>') . '
          </section>
        </div>';
    }

    /**
     * @param array{timeline: array<string, list<string>>, collisions: array<string, list<string>>} $audit
     */
    public static function renderCollisions(string $manifestText, array$audit): string
    {
        $collisionRows = '';
        foreach ($audit['collisions'] as$ts => $jobs) {$collisionRows .= '<div class="run-item"><span class="sand">' . ViewShell::esc($ts) . '</span><span>' . ViewShell::esc(implode(' + ', $jobs)) . '</span></div>';
        }
        if ($collisionRows === '') {$collisionRows = '<div style="color:#81b29a;">No overlapping job collisions detected in the next 12 ticks!</div>';
        }

        return '<div class="split-grid">
          <section class="card">
            <h2 style="font-size:1rem; margin-bottom:0.85rem;">Crontab Fleet Manifest (job_name = cron)</h2>
            <form class="cron-form" method="POST" action="/?tab=collisions">
              <label>
                Define Multiple Scheduled Jobs
                <textarea name="manifest" rows="6" required>' . ViewShell::esc($manifestText) . '</textarea>
              </label>
              <button type="submit" class="btn-primary">Detect Overlapping Execution Spikes</button>
            </form>
          </section>

          <section class="card">
            <h2 style="font-size:1rem; margin-bottom:0.85rem;">Detected Resource Contention Windows</h2>
            ' . $collisionRows . '
          </section>
        </div>';
    }

    public static function renderJsonTab(string $jsonPayload): string
    {
        return '<section class="card">
          <h2 style="font-size:1rem; margin-bottom:0.85rem;">Stateless JSON Schedule Payload (/api/schedule)</h2>
          <pre style="background:#0d0b0a; color:#81b29a; padding:1rem; border-radius:8px; overflow-x:auto; font-size:0.8rem;">' . ViewShell::esc($jsonPayload) . '</pre>
        </section>';
    }
}
