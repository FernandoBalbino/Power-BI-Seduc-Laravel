@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'error' => null,
])

@php
    $id = $attributes->get('id') ?? $name ?? 'input-'.\Illuminate\Support\Str::random(8);
@endphp

<div class="space-y-2">
    @if ($label)
        <label for="{{ $id }}" class="block text-[13px] font-semibold leading-5 text-slate-950">{{ $label }}</label>
    @endif

    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        {{ $attributes->merge(['class' => 'h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm text-slate-950 placeholder:text-slate-400 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100']) }}
    >

    @if ($error)
        <p class="text-xs font-medium text-red-600">{{ $error }}</p>
    @endif
</div>
