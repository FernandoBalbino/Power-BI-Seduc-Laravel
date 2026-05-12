@props(['variant' => 'neutral'])

@php
    $classes = [
        'success' => 'bg-green-100 text-green-800',
        'info' => 'bg-blue-100 text-blue-700',
        'warning' => 'bg-amber-100 text-amber-800',
        'danger' => 'bg-red-100 text-red-700',
        'purple' => 'bg-violet-100 text-violet-800',
        'neutral' => 'bg-slate-100 text-slate-600',
    ][$variant] ?? 'bg-slate-100 text-slate-600';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex h-6 items-center rounded-full px-2.5 text-xs font-semibold {$classes}"]) }}>
    {{ $slot }}
</span>
