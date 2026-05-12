@props([
    'withText' => true,
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'h-8 w-8',
        'md' => 'h-12 w-12',
        'lg' => 'h-14 w-14',
    ];

    $imageSize = $sizes[$size] ?? $sizes['md'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <img src="{{ asset('images/seduc-bi-logo.svg') }}" alt="SEDUC BI" class="{{ $imageSize }} shrink-0">

    @if ($withText)
        <span class="text-xl font-extrabold tracking-normal text-slate-950">SEDUC BI</span>
    @endif
</span>
