@props(['padding' => 'p-5'])

<section {{ $attributes->merge(['class' => "rounded-2xl border border-slate-200 bg-white {$padding} shadow-seduc-card"]) }}>
    {{ $slot }}
</section>
