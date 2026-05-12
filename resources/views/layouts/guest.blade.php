<x-guest-layout :title="$title ?? config('app.name', 'SEDUC BI')">
    {{ $slot }}
</x-guest-layout>
