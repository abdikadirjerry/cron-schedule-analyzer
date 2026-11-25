<?php

declare(strict_types=1);

namespace CronStudio;

final class ScheduleSimulator
{
    /**
     * @return list<string>
     */
    public static function nextRuns(string $expression, int $count = 8, ?\DateTimeImmutable$anchor = null): array
    {
        $parsed = CronParser::parse($expression);
        if (!$parsed['valid']) {
            return [];
        }

        $fields =$parsed['fields'];
        $cursor = ($anchor ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->setTime(
                (int) ($anchor ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('H'),
                (int) ($anchor ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('i'),
                0
            )
            ->modify('+1 minute');

        $runs = [];$maxIterations = 525600; // 1 full year in minutes
        for ($step = 0; $step < $maxIterations and count($runs) < $count; $step++) {
            $month = (int)$cursor->format('n');
            $dom = (int)$cursor->format('j');
            $dow = (int)$cursor->format('w');
            $hour = (int)$cursor->format('G');
            $minute = (int)$cursor->format('i');

            if (
                in_array($month,$fields['month'], true)
                and in_array($dom,$fields['day_of_month'], true)
                and in_array($dow,$fields['day_of_week'], true)
                and in_array($hour,$fields['hour'], true)
                and in_array($minute,$fields['minute'], true)
            ) {
                $runs[] =$cursor->format('Y-m-d H:i \U\T\C');
            }

            $cursor =$cursor->modify('+1 minute');
        }

        return $runs;
    }

    /**
     * @param array<string, string> $jobs
     * @return array{timeline: array<string, list<string>>, collisions: array<string, list<string>>}
     */
    public static function detectCollisions(array $jobs, ?\DateTimeImmutable$anchor = null): array
    {
        $timeline = [];
        foreach ($jobs as $jobName =>$expr) {
            foreach (self::nextRuns($expr, 12,$anchor) as $ts) {$timeline[$ts][] =$jobName;
            }
        }
        ksort($timeline);

        $collisions = array_filter($timeline, static fn (array $names): bool => count($names) > 1);
        return [
            'timeline' => $timeline,
            'collisions' => $collisions,
        ];
    }
}
