@props([
    'variant' => 'primary',
    'type' => 'button',
])

@php
    $classes = [
        'primary' => 'bg-seduc-primary text-white shadow-seduc-button hover:bg-seduc-primary-hover focus-visible:seduc-focus',
        'secondary' => 'border border-slate-200 bg-white text-slate-900 hover:bg-slate-50 focus-visible:seduc-focus',
        'ghost' => 'bg-transparent text-slate-700 hover:bg-slate-100 focus-visible:seduc-focus',
        'danger' => 'bg-red-500 text-white hover:bg-red-600 focus-visible:seduc-focus',
    ][$variant] ?? 'bg-seduc-primary text-white shadow-seduc-button hover:bg-seduc-primary-hover focus-visible:seduc-focus';
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => "inline-flex h-11 items-center justify-center gap-2 rounded-[10px] px-[18px] text-sm font-semibold transition {$classes}"]) }}
>
    {{ $slot }}
</button>
