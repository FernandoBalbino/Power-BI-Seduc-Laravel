@props(['name'])

@php
    $iconClass = $attributes->get('class', 'h-5 w-5');
@endphp

@switch($name)
    @case('book-open')
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14" /><path d="M3 18a2 2 0 0 1 2-2h5a2 2 0 0 1 2 2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2z" /><path d="M21 18a2 2 0 0 0-2-2h-5a2 2 0 0 0-2 2V5a2 2 0 0 1 2-2h5a2 2 0 0 1 2 2z" /></svg>
        @break
    @case('layout-dashboard')
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1" /><rect width="7" height="5" x="14" y="3" rx="1" /><rect width="7" height="9" x="14" y="12" rx="1" /><rect width="7" height="5" x="3" y="16" rx="1" /></svg>
        @break
    @case('plus-circle')
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><path d="M8 12h8" /><path d="M12 8v8" /></svg>
        @break
    @case('cloud-upload')
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 13v8" /><path d="m16 17-4-4-4 4" /><path d="M20.4 16.2A5 5 0 0 0 18 7h-1.3A8 8 0 1 0 4 14.9" /></svg>
        @break
    @case('building-2')
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18" /><path d="M6 12H4a2 2 0 0 0-2 2v8h20v-8a2 2 0 0 0-2-2h-2" /><path d="M10 6h4" /><path d="M10 10h4" /><path d="M10 14h4" /><path d="M10 18h4" /></svg>
        @break
    @case('users')
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.9" /><path d="M16 3.1a4 4 0 0 1 0 7.8" /></svg>
        @break
    @case('settings')
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12.2 2h-.4a2 2 0 0 0-2 1.7l-.1.7a2 2 0 0 1-3 1.2l-.6-.4a2 2 0 0 0-2.7.2l-.2.2a2 2 0 0 0-.2 2.7l.4.6a2 2 0 0 1-1.2 3l-.7.1a2 2 0 0 0-1.7 2v.4a2 2 0 0 0 1.7 2l.7.1a2 2 0 0 1 1.2 3l-.4.6a2 2 0 0 0 .2 2.7l.2.2a2 2 0 0 0 2.7.2l.6-.4a2 2 0 0 1 3 1.2l.1.7a2 2 0 0 0 2 1.7h.4a2 2 0 0 0 2-1.7l.1-.7a2 2 0 0 1 3-1.2l.6.4a2 2 0 0 0 2.7-.2l.2-.2a2 2 0 0 0 .2-2.7l-.4-.6a2 2 0 0 1 1.2-3l.7-.1a2 2 0 0 0 1.7-2v-.4a2 2 0 0 0-1.7-2l-.7-.1a2 2 0 0 1-1.2-3l.4-.6a2 2 0 0 0-.2-2.7l-.2-.2a2 2 0 0 0-2.7-.2l-.6.4a2 2 0 0 1-3-1.2l-.1-.7a2 2 0 0 0-2-1.7Z" /><circle cx="12" cy="12" r="3" /></svg>
        @break
    @case('log-out')
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><path d="m16 17 5-5-5-5" /><path d="M21 12H9" /></svg>
        @break
    @case('user')
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21a7 7 0 0 0-14 0" /><circle cx="12" cy="7" r="4" /></svg>
        @break
    @case('shield-check')
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.7 8.9a1 1 0 0 1-.6 0C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.2-2.7a1.2 1.2 0 0 1 1.6 0C14.5 3.8 17 5 19 5a1 1 0 0 1 1 1z" /><path d="m9 12 2 2 4-4" /></svg>
        @break
    @case('chart-bar')
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18" /><path d="M7 16V9" /><path d="M12 16V5" /><path d="M17 16v-3" /></svg>
        @break
    @case('pie-chart')
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12c.6 5-3.5 9-8.5 9A8.5 8.5 0 0 1 4 12.5C4 7.5 8 3.4 13 4v8z" /><path d="M21 12a9 9 0 0 0-9-9v9z" /></svg>
        @break
    @case('file-spreadsheet')
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><path d="M14 2v6h6" /><path d="M8 13h8" /><path d="M8 17h8" /><path d="M10 9H8" /></svg>
        @break
    @case('circle-dollar-sign')
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><path d="M16 8h-6a2 2 0 0 0 0 4h4a2 2 0 0 1 0 4H8" /><path d="M12 18V6" /></svg>
        @break
    @case('landmark')
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 22h18" /><path d="M6 18h12" /><path d="M6 10v8" /><path d="M10 10v8" /><path d="M14 10v8" /><path d="M18 10v8" /><path d="m12 2 9 5H3z" /></svg>
        @break
    @case('scale')
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m16 16 3-8 3 8c-.9.7-1.9 1-3 1s-2.1-.3-3-1" /><path d="m2 16 3-8 3 8c-.9.7-1.9 1-3 1s-2.1-.3-3-1" /><path d="M7 21h10" /><path d="M12 3v18" /><path d="M3 7h2c2 0 5-2 7-4 2 2 5 4 7 4h2" /></svg>
        @break
    @case('gauge')
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4" /><path d="M3.3 14a9 9 0 1 1 17.4 0" /><path d="M5 20h14" /></svg>
        @break
    @default
        <svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /></svg>
@endswitch
