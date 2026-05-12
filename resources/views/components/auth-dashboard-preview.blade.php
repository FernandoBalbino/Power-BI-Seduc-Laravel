@props(['class' => ''])

<div {{ $attributes->merge(['class' => "rounded-2xl border border-slate-200 bg-white p-3 shadow-seduc-card {$class}"]) }}>
    <div class="grid grid-cols-4 gap-2.5">
        <div class="rounded-xl border border-slate-200 bg-white p-2.5">
            <div class="flex items-start gap-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-[10px] bg-blue-100 text-seduc-primary">
                    <x-icon name="circle-dollar-sign" class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                    <p class="truncate text-[10px] font-semibold text-slate-500">Investimento Previsto</p>
                    <p class="mt-1 whitespace-nowrap text-sm font-bold text-slate-950">R$ 419 mi</p>
                    <p class="whitespace-nowrap text-[9px] text-slate-500">100% do total</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-2.5">
            <div class="flex items-start gap-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-[10px] bg-green-100 text-green-600">
                    <x-icon name="landmark" class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                    <p class="truncate text-[10px] font-semibold text-slate-500">Total já Pago</p>
                    <p class="mt-1 whitespace-nowrap text-sm font-bold text-slate-950">R$ 186 mi</p>
                    <p class="whitespace-nowrap text-[9px] text-slate-500">44,39% do previsto</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-2.5">
            <div class="flex items-start gap-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-[10px] bg-amber-100 text-amber-500">
                    <x-icon name="scale" class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                    <p class="truncate text-[10px] font-semibold text-slate-500">Saldo</p>
                    <p class="mt-1 whitespace-nowrap text-sm font-bold text-slate-950">R$ 232 mi</p>
                    <p class="whitespace-nowrap text-[9px] text-slate-500">55,61% do previsto</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-2.5">
            <div class="flex items-start gap-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-[10px] bg-violet-100 text-violet-600">
                    <x-icon name="gauge" class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                    <p class="truncate text-[10px] font-semibold text-slate-500">Execução Média</p>
                    <p class="mt-1 whitespace-nowrap text-sm font-bold text-slate-950">53,62%</p>
                    <p class="whitespace-nowrap text-[9px] text-slate-500">Média ponderada</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3 grid grid-cols-3 gap-2.5">
        <div class="rounded-xl border border-slate-200 bg-white p-3">
            <p class="text-xs font-bold text-slate-800">Valor Pago por Município</p>
            <div class="mt-4 grid h-32 grid-cols-[34px_1fr] gap-2">
                <div class="flex flex-col justify-between text-[10px] text-slate-500">
                    <span>R$ 60M</span>
                    <span>R$ 40M</span>
                    <span>R$ 20M</span>
                    <span>R$ 0</span>
                </div>
                <div class="relative flex items-end gap-3 border-l border-b border-slate-200 px-2">
                    <span class="absolute left-0 right-0 top-0 border-t border-dashed border-slate-200"></span>
                    <span class="absolute left-0 right-0 top-1/3 border-t border-dashed border-slate-200"></span>
                    <span class="absolute left-0 right-0 top-2/3 border-t border-dashed border-slate-200"></span>
                    @foreach ([82, 58, 47, 34, 24, 75] as $height)
                        <div class="relative z-10 flex-1 rounded-t-md bg-seduc-primary shadow-[0_6px_14px_rgba(13,110,253,0.18)]" style="height: {{ $height }}%"></div>
                    @endforeach
                </div>
            </div>
            <div class="ml-10 mt-2 grid grid-cols-6 gap-1 text-center text-[8px] leading-3 text-slate-500">
                <span>Maceió</span>
                <span>Arap.</span>
                <span>Penedo</span>
                <span>Marag.</span>
                <span>S. Miguel</span>
                <span>Outros</span>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-3">
            <p class="text-xs font-bold text-slate-800">Status das Obras</p>
            <div class="mt-4 flex h-32 items-center gap-4">
                <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full bg-[conic-gradient(#0D6EFD_0_50%,#16A34A_50%_80%,#EF4444_80%_96%,#F59E0B_96%_100%)]">
                    <div class="flex h-14 w-14 flex-col items-center justify-center rounded-full bg-white shadow-inner">
                        <span class="text-xl font-bold leading-5 text-slate-950">54</span>
                        <span class="text-[10px] text-slate-500">Obras</span>
                    </div>
                </div>
                <div class="min-w-0 flex-1 space-y-2 text-[9px] text-slate-600">
                    <div class="grid grid-cols-[10px_1fr_16px] items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-seduc-primary"></span>
                        <span>Em andamento</span>
                        <span>27</span>
                    </div>
                    <div class="grid grid-cols-[10px_1fr_16px] items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-green-600"></span>
                        <span>Concluída</span>
                        <span>16</span>
                    </div>
                    <div class="grid grid-cols-[10px_1fr_16px] items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                        <span>Não iniciada</span>
                        <span>10</span>
                    </div>
                    <div class="grid grid-cols-[10px_1fr_16px] items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                        <span>Em licitação</span>
                        <span>1</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-3">
            <p class="text-xs font-bold text-slate-800">Evolução dos Pagamentos</p>
            <div class="mt-4 grid h-32 grid-cols-[34px_1fr] gap-2">
                <div class="flex flex-col justify-between text-[10px] text-slate-500">
                    <span>R$ 80M</span>
                    <span>R$ 60M</span>
                    <span>R$ 40M</span>
                    <span>R$ 0</span>
                </div>
                <div class="relative border-l border-b border-slate-200">
                    <span class="absolute left-0 right-0 top-0 border-t border-dashed border-slate-200"></span>
                    <span class="absolute left-0 right-0 top-1/3 border-t border-dashed border-slate-200"></span>
                    <span class="absolute left-0 right-0 top-2/3 border-t border-dashed border-slate-200"></span>
                    <svg class="absolute inset-0 h-full w-full overflow-visible" viewBox="0 0 260 130" preserveAspectRatio="none" aria-hidden="true">
                        <path d="M10 116 C38 104, 45 94, 68 88 S104 58, 127 52 S163 38, 186 31 S224 17, 250 8" fill="none" stroke="#0D6EFD" stroke-width="4" stroke-linecap="round" />
                        <path d="M10 116 C38 104, 45 94, 68 88 S104 58, 127 52 S163 38, 186 31 S224 17, 250 8 L250 130 L10 130 Z" fill="rgba(13,110,253,0.12)" />
                    </svg>
                    @foreach ([[4, 88], [23, 78], [43, 64], [62, 47], [81, 35], [96, 24]] as [$left, $top])
                        <span class="absolute h-2.5 w-2.5 rounded-full border-2 border-white bg-seduc-primary shadow" style="left: {{ $left }}%; top: {{ $top }}%;"></span>
                    @endforeach
                </div>
            </div>
            <div class="ml-10 mt-2 grid grid-cols-4 text-center text-[8px] leading-3 text-slate-500">
                <span>Jan/25</span>
                <span>Mai/25</span>
                <span>Set/25</span>
                <span>Dez/25</span>
            </div>
        </div>
    </div>
</div>
