<?php

// app/Services/MetricComparisonService.php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MetricComparisonService
{
    /**
     * Compare a metric for the given month vs the previous month.
     *
     * @param  Builder       $query    Eloquent query (scoped or unscoped)
     * @param  string        $dateCol  The date column (e.g. 'created_at')
     * @param  Carbon|null   $month    Reference month (defaults to now)
     * @param  string        $agg      'count' | 'sum' | 'avg' — the aggregate to compute
     * @param  string|null   $sumCol   Column to sum/average (ignored when agg='count')
     * @return array  ['current' => float|int, 'previous' => float|int, 'trend' => 'up'|'down'|'flat', 'pct' => int]
     */
    public function compare(
        Builder $query,
        string $dateCol = 'created_at',
        ?Carbon $month = null,
        string $agg = 'count',
        ?string $sumCol = null
    ): array {
        $month = $month ?? now();

        $currentStart = $month->copy()->startOfMonth();
        $currentEnd   = $month->copy()->endOfMonth();

        $prevMonth    = $month->copy()->subMonth();
        $prevStart    = $prevMonth->copy()->startOfMonth();
        $prevEnd      = $prevMonth->copy()->endOfMonth();

        $current = $this->aggregate(
            (clone $query)->whereBetween($dateCol, [$currentStart, $currentEnd]),
            $agg,
            $sumCol
        );

        $previous = $this->aggregate(
            (clone $query)->whereBetween($dateCol, [$prevStart, $prevEnd]),
            $agg,
            $sumCol
        );

        $trend = 'flat';
        $pct   = 0;

        if ($previous != 0) {
            $pct   = round((($current - $previous) / $previous) * 100);
            $trend = $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'flat');
        } elseif ($current > 0) {
            $trend = 'up';
            $pct   = 100;
        }

        return [
            'current'  => $current,
            'previous' => $previous,
            'trend'    => $trend,
            'pct'      => abs($pct),
        ];
    }

    /**
     * Aggregate a query
     */
    private function aggregate(Builder $query, string $agg, ?string $col): float|int
    {
        return match ($agg) {
            'sum'   => (float) $query->sum($col ?? 'total'),
            'avg'   => (float) $query->avg($col ?? 'total'),
            default => (int) $query->count(),
        };
    }
}
