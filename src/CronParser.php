<?php

declare(strict_types=1);

namespace CronStudio;

final class CronParser
{
    private const FIELD_RANGES = [
        'minute' => [0, 59],
        'hour' => [0, 23],
        'day_of_month' => [1, 31],
        'month' => [1, 12],
        'day_of_week' => [0, 6],
    ];

    /**
     * @return array{valid: bool, error: ?string, fields: array<string, list<int>>, human: string}
     */
    public static function parse(string $expression): array
    {
        $clean = preg_replace('/\s+/', ' ', trim($expression)) ?? '';$parts = $clean === '' ? [] : explode(' ',$clean);

        if (count($parts) !== 5) {
            return [
                'valid' => false,
                'error' => 'Cron expression must have exactly 5 fields: minute hour day-of-month month day-of-week.',
                'fields' => [],
                'human' => 'Invalid expression',
            ];
        }

        $parsedFields = [];$keys = array_keys(self::FIELD_RANGES);

        foreach ($keys as $idx =>$name) {
            [$min, $max] = self::FIELD_RANGES[$name];
            try {
                $parsedFields[$name] = self::expandSegment($parts[$idx], $min,$max);
            } catch (\InvalidArgumentException $e) {
                return [
                    'valid' => false,
                    'error' => "Invalid {$name} field ('{$parts[$idx]}'): " . $e->getMessage(),
                    'fields' => [],
                    'human' => 'Invalid expression',
                ];
            }
        }

        return [
            'valid' => true,
            'error' => null,
            'fields' => $parsedFields,
            'human' => self::summarize($parts),
        ];
    }

    /**
     * @return list<int>
     */
    private static function expandSegment(string $segment, int $min, int$max): array
    {
        $values = [];
        foreach (explode(',', $segment) as$token) {
            $token = trim($token);
            if ($token === '') {
                throw new \InvalidArgumentException('Empty list item.');
            }

            $step = 1;
            if (str_contains($token, '/')) {
                $stepParts = explode('/',$token);
                if (count($stepParts) !== 2 or !ctype_digit($stepParts[1]) or (int)$stepParts[1] < 1) {
                    throw new \InvalidArgumentException('Step divisor must be a positive integer.');
                }
                $token =$stepParts[0];
                $step = (int)$stepParts[1];
            }

            if ($token === '*') {
                $start =$min;
                $end =$max;
            } elseif (str_contains($token, '-')) {
                $rangeParts = explode('-',$token);
                if (count($rangeParts) !== 2 or !ctype_digit($rangeParts[0]) or !ctype_digit($rangeParts[1])) {
                    throw new \InvalidArgumentException('Range bounds must be integers.');
                }
                $start = (int)$rangeParts[0];
                $end = (int)$rangeParts[1];
                if ($start >$end or $start <$min or $end >$max) {
                    throw new \InvalidArgumentException("Range {$start}-{$end} out of bounds [{$min}..{$max}].");
                }
            } elseif (ctype_digit($token)) {
                $val = (int)$token;
                if ($val <$min or $val >$max) {
                    throw new \InvalidArgumentException("Value {$val} out of bounds [{$min}..{$max}].");
                }
                $start =$val;
                $end =$val;
            } else {
                throw new \InvalidArgumentException("Unrecognized token '{$token}'.");
            }

            for ($i = $start; $i <= $end; $i += $step) {$values[$i] =$i;
            }
        }

        ksort($values);
        return array_values($values);
    }

    /**
     * @param list<string> $parts
     */
    private static function summarize(array $parts): string
    {
        [$min,$hour, $dom,$month, $dow] =$parts;
        $timeDesc = ($min === '*' and$hour === '*')
            ? 'Every minute'
            : (str_starts_with($min, '*/') and$hour === '*'
                ? 'Every ' . substr($min, 2) . ' minutes'
                : "At minute [{$min}] of hour [{$hour}]");
        return "{$timeDesc} \vert{} DOM: {$dom} | Month: {$month} \vert{} DOW: {$dow}";
    }
}
