@php
    $user = auth()->user();
    $initials = collect(explode(' ', $user?->name ?? 'Usuário'))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->join('');
@endphp

<aside class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-slate-200 bg-white px-3.5 py-[18px] shadow-[1px_0_0_rgba(226,232,240,0.9)]">
    <a href="{{ route('dashboard') }}" class="flex h-11 items-center gap-3 px-2 text-slate-950">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-seduc-primary-soft text-seduc-primary">
            <x-icon name="book-open" class="h-6 w-6" />
        </span>
        <span class="text-xl font-extrabold tracking-normal">SEDUC BI</span>
    </a>

    <nav class="mt-8 flex flex-1 flex-col gap-2">
        @foreach (config('seduc-bi.menu') as $item)
            @php
                $isActive = collect($item['active'])->contains(fn ($pattern) => request()->routeIs($pattern));
                $href = Route::has($item['route']) ? route($item['route']) : route('dashboard');
            @endphp

            <a
                href="{{ $href }}"
                class="{{ $isActive ? 'bg-seduc-primary text-white shadow-seduc-button' : 'text-slate-700 hover:bg-slate-100' }} flex h-12 items-center gap-3 rounded-[10px] px-3.5 text-sm font-semibold transition"
            >
                <x-icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="rounded-[14px] border border-slate-200 bg-white p-3">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-600 text-sm font-bold text-white">
                {{ $initials ?: 'SB' }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-bold text-slate-950">{{ $user?->name }}</p>
                <p class="truncate text-xs text-slate-500">{{ $user?->role?->label() ?? 'Setor' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="flex h-10 w-full items-center justify-center gap-2 rounded-[10px] text-sm font-semibold text-slate-600 transition hover:bg-slate-100">
                <x-icon name="log-out" class="h-4 w-4" />
                Sair
            </button>
        </form>
    </div>
</aside>
