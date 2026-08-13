<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\GradingScale;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ChartDataService
{
    protected const COLORS = [
        'present' => '#059669', 'paid' => '#059669', 'emerald' => '#059669',
        'absent' => '#dc2626', 'outstanding' => '#dc2626', 'red' => '#dc2626',
        'late' => '#f59e0b', 'amber' => '#f59e0b',
        'excused' => '#2563eb', 'due' => '#2563eb', 'blue' => '#2563eb',
        'purple' => '#9333ea', 'gray' => '#9ca3af',
    ];

    /**
     * Daily present/absent/late/excused counts for the last N days, for the given term.
     */
    public function attendanceTrend(?Term $term, int $days = 14): array
    {
        $today = Carbon::today();
        $start = $today->copy()->subDays($days - 1);

        $rows = $term
            ? Attendance::where('term_id', $term->id)
                ->where('date', '>=', $start)
                ->selectRaw('date, status, count(*) as total')
                ->groupBy('date', 'status')
                ->get()
                ->groupBy(fn ($row) => $row->date->format('Y-m-d'))
            : collect();

        $labels = [];
        $statuses = ['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused'];
        $series = array_fill_keys(array_keys($statuses), []);

        for ($date = $start->copy(); $date->lte($today); $date->addDay()) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            $dayRows = $rows->get($key, collect())->keyBy('status');

            foreach ($statuses as $status => $label) {
                $series[$status][] = (int) ($dayRows->get($status)?->total ?? 0);
            }
        }

        $datasets = collect($statuses)->map(fn ($label, $status) => [
            'label' => $label,
            'data' => $series[$status],
            'borderColor' => self::COLORS[$status],
            'backgroundColor' => self::COLORS[$status],
            'tension' => 0.3,
        ])->values()->all();

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    /**
     * Overall status totals (present/absent/late/excused) for an already-filtered attendance query.
     */
    public function attendanceStatusDistribution(Builder $query): array
    {
        $totals = (clone $query)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statuses = ['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused'];

        return [
            'labels' => array_values($statuses),
            'datasets' => [[
                'data' => collect($statuses)->keys()->map(fn ($status) => (int) ($totals[$status] ?? 0))->all(),
                'backgroundColor' => collect($statuses)->keys()->map(fn ($status) => self::COLORS[$status])->all(),
            ]],
        ];
    }

    /**
     * Number of active students per school class.
     */
    public function studentsByClass(): array
    {
        $counts = Student::active()
            ->join('class_arms', 'class_arms.id', '=', 'students.current_class_arm_id')
            ->join('classes', 'classes.id', '=', 'class_arms.school_class_id')
            ->select(['classes.id', 'classes.name', 'classes.order'])
            ->selectRaw('count(*) as total')
            ->groupBy('classes.id', 'classes.name', 'classes.order')
            ->orderBy('classes.order')
            ->get();

        return [
            'labels' => $counts->pluck('name')->all(),
            'datasets' => [[
                'label' => 'Students',
                'data' => $counts->pluck('total')->map(fn ($n) => (int) $n)->all(),
                'backgroundColor' => self::COLORS['emerald'],
            ]],
        ];
    }

    /**
     * Grade distribution for a (possibly filtered) results query, ordered by the grading scale.
     */
    public function gradeDistribution(Builder $query): array
    {
        $grades = GradingScale::orderBy('order')->pluck('grade');

        if ($grades->isEmpty()) {
            $grades = (clone $query)->distinct()->orderBy('grade')->pluck('grade')->filter();
        }

        $counts = (clone $query)
            ->whereNotNull('grade')
            ->selectRaw('grade, count(*) as total')
            ->groupBy('grade')
            ->pluck('total', 'grade');

        $palette = [self::COLORS['emerald'], self::COLORS['blue'], self::COLORS['amber'], self::COLORS['red'], self::COLORS['purple'], self::COLORS['gray']];

        return [
            'labels' => $grades->all(),
            'datasets' => [[
                'label' => 'Students',
                'data' => $grades->map(fn ($grade) => (int) ($counts[$grade] ?? 0))->all(),
                'backgroundColor' => $grades->map(fn ($grade, $i) => $palette[$i % count($palette)])->values()->all(),
            ]],
        ];
    }

    /**
     * Due vs. paid amounts per fee category, from an already-computed [name => ['due'=>, 'paid'=>]] map.
     */
    public function feesByCategory(Collection $byCategory): array
    {
        return [
            'labels' => $byCategory->keys()->all(),
            'datasets' => [
                ['label' => 'Due', 'data' => $byCategory->pluck('due')->map(fn ($v) => (float) $v)->all(), 'backgroundColor' => self::COLORS['blue']],
                ['label' => 'Collected', 'data' => $byCategory->pluck('paid')->map(fn ($v) => (float) $v)->all(), 'backgroundColor' => self::COLORS['emerald']],
            ],
        ];
    }
}
