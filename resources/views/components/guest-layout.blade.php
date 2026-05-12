@props(['title' => 'SEDUC BI'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-seduc-page text-seduc-body">
        <main class="min-h-screen px-6 py-8">
            <div class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-7xl grid-cols-1 items-center gap-8 lg:grid-cols-[540px_1fr]">
                <section class="rounded-[22px] border border-slate-200 bg-white p-8 shadow-[0_18px_48px_rgba(15,23,42,0.08)]">
                    {{ $slot }}
                </section>

                <section class="hidden min-h-[720px] overflow-hidden rounded-[22px] border border-blue-100 bg-seduc-soft-blue p-12 shadow-seduc-card lg:block">
                    <div class="mb-8 inline-flex items-center gap-2 rounded-xl bg-seduc-primary-soft px-4 py-3 text-sm font-bold text-seduc-primary">
                        <x-icon name="chart-bar" class="h-5 w-5" />
                        Bem-vindo ao SEDUC BI
                    </div>

                    <h1 class="max-w-3xl text-[40px] font-extrabold leading-[52px] text-slate-950">
                        Visualize dados, acompanhe obras e tome
                        <span class="text-seduc-primary">decisões com clareza</span>
                    </h1>

                    <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
                        Dashboards interativos, filtros inteligentes, gráficos intuitivos e atualizações em tempo real para uma gestão mais eficiente e transparente.
                    </p>

                    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-4 shadow-seduc-card">
                        <div class="grid grid-cols-4 gap-3">
                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-seduc-primary">
                                    <x-icon name="circle-dollar-sign" class="h-5 w-5" />
                                </div>
                                <p class="text-xs font-semibold text-slate-500">Investimento Previsto</p>
                                <p class="mt-1 text-lg font-bold text-slate-950">R$ 419 mi</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-green-100 text-green-600">
                                    <x-icon name="landmark" class="h-5 w-5" />
                                </div>
                                <p class="text-xs font-semibold text-slate-500">Total já Pago</p>
                                <p class="mt-1 text-lg font-bold text-slate-950">R$ 186 mi</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-500">
                                    <x-icon name="scale" class="h-5 w-5" />
                                </div>
                                <p class="text-xs font-semibold text-slate-500">Saldo</p>
                                <p class="mt-1 text-lg font-bold text-slate-950">R$ 232 mi</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                                    <x-icon name="gauge" class="h-5 w-5" />
                                </div>
                                <p class="text-xs font-semibold text-slate-500">Execução Média</p>
                                <p class="mt-1 text-lg font-bold text-slate-950">53,62%</p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-3">
                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <p class="text-xs font-bold text-slate-700">Valor pago por município</p>
                                <div class="mt-6 flex h-28 items-end gap-3">
                                    @foreach ([78, 58, 46, 36, 28, 70] as $height)
                                        <div class="flex-1 rounded-t-lg bg-seduc-primary" style="height: {{ $height }}%"></div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <p class="text-xs font-bold text-slate-700">Status das obras</p>
                                <div class="mt-5 flex items-center gap-4">
                                    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-[conic-gradient(#0D6EFD_0_50%,#16A34A_50%_80%,#EF4444_80%_96%,#F59E0B_96%_100%)]">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white text-sm font-bold text-slate-950">54</div>
                                    </div>
                                    <div class="space-y-2 text-xs text-slate-600">
                                        <p><span class="mr-2 inline-block h-2 w-2 rounded-full bg-seduc-primary"></span>Em andamento</p>
                                        <p><span class="mr-2 inline-block h-2 w-2 rounded-full bg-green-600"></span>Concluída</p>
                                        <p><span class="mr-2 inline-block h-2 w-2 rounded-full bg-red-500"></span>Não iniciada</p>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <p class="text-xs font-bold text-slate-700">Evolução dos pagamentos</p>
                                <div class="mt-6 flex h-28 items-end gap-2">
                                    @foreach ([16, 26, 42, 50, 60, 68, 78, 88] as $height)
                                        <div class="flex-1 rounded-t-full bg-seduc-primary" style="height: {{ $height }}%"></div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 grid grid-cols-3 gap-6">
                        <div class="flex items-start gap-3">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-seduc-primary shadow-seduc-card">
                                <x-icon name="pie-chart" class="h-6 w-6" />
                            </span>
                            <div>
                                <p class="font-bold text-slate-900">Dashboards dinâmicos</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600">Visualize KPIs e indicadores em painéis interativos.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-green-600 shadow-seduc-card">
                                <x-icon name="file-spreadsheet" class="h-6 w-6" />
                            </span>
                            <div>
                                <p class="font-bold text-slate-900">Importação de planilhas</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600">Importe dados com facilidade e mantenha tudo atualizado.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-violet-600 shadow-seduc-card">
                                <x-icon name="users" class="h-6 w-6" />
                            </span>
                            <div>
                                <p class="font-bold text-slate-900">Análises por setor</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600">Acompanhe informações estratégicas do seu setor.</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        @livewireScripts
    </body>
</html>
