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
    <body class="h-screen overflow-hidden bg-seduc-page text-seduc-body">
        <main class="h-screen px-6 py-6">
            <div class="mx-auto grid h-full max-w-7xl grid-cols-1 items-center gap-8 lg:grid-cols-[500px_1fr]">
                <section class="flex max-h-[calc(100vh-3rem)] min-h-0 items-center rounded-[22px] border border-slate-200 bg-white p-7 shadow-[0_18px_48px_rgba(15,23,42,0.08)]">
                    {{ $slot }}
                </section>

                <section class="hidden h-full min-h-0 overflow-hidden rounded-[22px] border border-blue-100 bg-seduc-soft-blue p-8 shadow-seduc-card lg:flex lg:flex-col lg:justify-center">
                    <div class="mb-5 inline-flex w-fit items-center gap-2 rounded-xl bg-seduc-primary-soft px-4 py-3 text-sm font-bold text-seduc-primary">
                        <x-icon name="chart-bar" class="h-5 w-5" />
                        Bem-vindo ao SEDUC BI
                    </div>

                    <h1 class="max-w-3xl text-[34px] font-extrabold leading-[42px] text-slate-950">
                        Visualize dados, acompanhe obras e tome
                        <span class="text-seduc-primary">decisões com clareza</span>
                    </h1>

                    <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">
                        Dashboards interativos, filtros inteligentes, gráficos intuitivos e atualizações em tempo real para uma gestão mais eficiente e transparente.
                    </p>

                    <x-auth-dashboard-preview class="mt-6" />

                    <div class="mt-6 grid grid-cols-3 gap-5">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-seduc-primary shadow-seduc-card">
                                <x-icon name="pie-chart" class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="text-sm font-bold text-slate-900">Dashboards dinâmicos</p>
                                <p class="mt-1 text-xs leading-5 text-slate-600">Visualize KPIs e indicadores em painéis interativos.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-green-600 shadow-seduc-card">
                                <x-icon name="file-spreadsheet" class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="text-sm font-bold text-slate-900">Importação de planilhas</p>
                                <p class="mt-1 text-xs leading-5 text-slate-600">Importe dados com facilidade e mantenha tudo atualizado.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-violet-600 shadow-seduc-card">
                                <x-icon name="users" class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="text-sm font-bold text-slate-900">Análises por setor</p>
                                <p class="mt-1 text-xs leading-5 text-slate-600">Acompanhe informações estratégicas do seu setor.</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        @livewireScripts
    </body>
</html>
