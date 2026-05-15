@props(['widget', 'data'])

@php
    $chartType = $widget->chart_type->value;
    $colors = config('seduc-bi.chart_colors', ['#0D6EFD', '#16A34A', '#EF4444', '#F59E0B', '#7C3AED', '#06B6D4', '#64748B']);
    $apexType = $widget->chart_type->apexType();
    $chartOptions = null;

    if ($apexType) {
        $chartOptions = [
            'chart' => [
                'type' => $apexType,
                'height' => 280,
                'fontFamily' => 'Inter, sans-serif',
                'toolbar' => ['show' => false],
            ],
            'colors' => $colors,
            'dataLabels' => ['enabled' => false],
            'grid' => [
                'borderColor' => '#E2E8F0',
                'strokeDashArray' => 4,
            ],
            'legend' => [
                'fontFamily' => 'Inter, sans-serif',
                'fontSize' => '13px',
                'labels' => ['colors' => '#334155'],
            ],
            'tooltip' => ['theme' => 'light'],
            'series' => $data['series'] ?? [],
        ];

        if (in_array($chartType, ['pie', 'donut'], true)) {
            $chartOptions['labels'] = $data['labels'] ?? [];
            $chartOptions['series'] = $data['series'] ?? [];
            $chartOptions['plotOptions'] = [
                'pie' => [
                    'donut' => ['size' => '68%'],
                ],
            ];
        } else {
            $chartOptions['xaxis'] = [
                'categories' => $data['categories'] ?? [],
                'labels' => ['style' => ['colors' => '#64748B', 'fontSize' => '12px']],
            ];
            $chartOptions['yaxis'] = [
                'labels' => ['style' => ['colors' => '#64748B', 'fontSize' => '12px']],
            ];
            $chartOptions['stroke'] = [
                'curve' => 'smooth',
                'width' => in_array($chartType, ['line', 'area'], true) ? 3 : 0,
            ];
            $chartOptions['plotOptions'] = [
                'bar' => [
                    'borderRadius' => 6,
                    'columnWidth' => '48%',
                ],
            ];
            $chartOptions['fill'] = [
                'opacity' => $chartType === 'area' ? 0.18 : 1,
            ];
        }
    }
@endphp

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white p-5 shadow-seduc-card']) }}>
    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 class="text-base font-bold text-slate-950">{{ $widget->title }}</h3>
            <p class="mt-1 text-xs font-semibold text-slate-500">{{ $widget->chart_type->label() }}</p>
        </div>

        <x-badge variant="neutral">{{ $widget->width }}x{{ $widget->height }}</x-badge>
    </div>

    @if ($chartType === 'card')
        <div class="mt-6 rounded-2xl bg-blue-50 p-5">
            <p class="text-sm font-semibold text-slate-600">{{ $data['aggregation'] ?? 'Resumo' }}</p>
            <p class="mt-2 text-3xl font-bold text-slate-950">{{ $data['formatted'] ?? '0' }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $data['label'] ?? 'Indicador' }}</p>
        </div>
    @elseif ($chartType === 'table')
        <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        @foreach (($data['headers'] ?? ['Grupo', 'Valor']) as $header)
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-600">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse (($data['rows'] ?? []) as $row)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm font-semibold text-slate-800">{{ $row['label'] }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $row['formatted'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-8 text-center text-sm text-slate-500">Sem dados para exibir.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div
            wire:ignore
            class="mt-5 min-h-[280px]"
            x-data
            x-init="$nextTick(() => window.SeducCharts?.render($refs.chart, @js($chartOptions)))"
        >
            <div x-ref="chart" class="min-h-[280px]"></div>
        </div>
    @endif
</section>
