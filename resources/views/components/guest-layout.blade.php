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
    <body class="h-screen overflow-hidden bg-white text-seduc-body">
        <main class="h-screen p-4">
            <div class="grid h-full grid-cols-1 gap-4 lg:grid-cols-[49%_51%]">
                <section class="relative flex h-full min-h-0 flex-col bg-white px-8 py-7">
                    <a href="{{ route('login') }}" wire:navigate class="absolute left-8 top-7">
                        <x-logo size="sm" />
                    </a>

                    <div class="flex flex-1 items-center justify-center">
                        {{ $slot }}
                    </div>

                    <footer class="flex items-center justify-between text-xs text-slate-400">
                        <span>Copyright © {{ now()->year }} SEDUC BI</span>
                        <span>Política de privacidade</span>
                    </footer>
                </section>

                <section class="relative hidden h-full min-h-0 overflow-hidden rounded-[22px] bg-[#302EF4] px-14 py-12 text-white shadow-[0_24px_60px_rgba(13,20,120,0.24)] lg:flex lg:flex-col lg:justify-center">
                    <div class="pointer-events-none absolute inset-0 opacity-35">
                        <div class="absolute -left-28 top-10 h-80 w-80 rounded-full bg-white/10"></div>
                        <div class="absolute right-8 top-0 h-48 w-48 rounded-bl-[90px] bg-white/10"></div>
                        <div class="absolute bottom-8 left-20 h-36 w-56 rounded-[28px] bg-white/10"></div>
                        <div class="absolute bottom-28 right-24 h-32 w-44 rounded-[28px] border border-dashed border-white/30"></div>
                        <div class="absolute left-10 top-1/2 h-48 w-32 rounded-r-[80px] bg-white/10"></div>
                    </div>

                    <div class="relative z-10 mx-auto w-full max-w-[620px]">
                        <h1 class="max-w-xl text-[34px] font-semibold leading-[44px] text-white">
                            Visualize dados, acompanhe obras e tome decisões com clareza.
                        </h1>

                        <p class="mt-4 max-w-lg text-sm leading-6 text-white/75">
                            Acesse seus dashboards, acompanhe indicadores do setor e mantenha as informações sempre atualizadas.
                        </p>

                        <div class="relative mt-8">
                            <div class="absolute -inset-5 rounded-[28px] bg-white/10 blur-md"></div>
                            <div class="relative rounded-xl border border-white/25 bg-white p-2 shadow-[0_28px_70px_rgba(0,0,0,0.22)]">
                                <img
                                    src="{{ asset('images/auth-dashboard.jpg') }}"
                                    alt="Dashboard de indicadores"
                                    class="h-[300px] w-full rounded-lg object-cover object-center"
                                >
                            </div>

                            <div class="absolute -right-10 top-16 w-48 rounded-xl border border-white/20 bg-white p-4 text-slate-950 shadow-[0_18px_50px_rgba(15,23,42,0.20)]">
                                <p class="text-[11px] font-semibold text-slate-500">Execução média</p>
                                <div class="mt-3 flex items-center justify-center">
                                    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-[conic-gradient(#302EF4_0_72%,#DAD7FF_72%_100%)]">
                                        <div class="flex h-16 w-16 flex-col items-center justify-center rounded-full bg-white">
                                            <span class="text-lg font-bold text-slate-950">72%</span>
                                            <span class="text-[10px] text-slate-500">Obras</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 grid grid-cols-3 gap-4 text-sm">
                            <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                                <p class="font-semibold text-white">Dashboards</p>
                                <p class="mt-1 text-xs leading-5 text-white/70">KPIs e indicadores por setor.</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                                <p class="font-semibold text-white">Planilhas</p>
                                <p class="mt-1 text-xs leading-5 text-white/70">Importação simples e segura.</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                                <p class="font-semibold text-white">Análises</p>
                                <p class="mt-1 text-xs leading-5 text-white/70">Decisões claras e rápidas.</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        @livewireScripts
    </body>
</html>
