@props(['type' => 'bar', 'labels' => [], 'datasets' => [], 'options' => [], 'height' => 260])

@php
    $config = [
        'type' => $type,
        'data' => [
            'labels' => $labels,
            'datasets' => $datasets,
        ],
        'options' => array_replace_recursive([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => count($datasets) > 1,
                    'position' => 'bottom',
                    'labels' => ['boxWidth' => 12, 'padding' => 12],
                ],
            ],
        ], $options),
    ];
@endphp

<div style="height: {{ $height }}px" {{ $attributes }}>
    @if(empty($labels))
        <x-empty-state title="Not enough data yet" description="Charts will appear once there is activity to show." />
    @else
        <canvas data-chart="{{ json_encode($config) }}"></canvas>
    @endif
</div>
