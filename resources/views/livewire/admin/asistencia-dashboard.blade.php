<div class="p-6 pt-12 bg-gray-50 min-h-screen font-sans">
    <div class="max-w-7xl mx-auto">
        <!-- TÍTULO Y ESTADO DEL SISTEMA -->
        <div class="flex flex-col md:flex-row items-center justify-between mb-10 gap-4">
            <div class="">
                <h1 class="text-2xl font-bold text-indigo-600 tracking-tight">Monitor En Tiempo Real</h1>
                <span class="text-xs">Motor Gas Company — Gestión de Personal</span>
            </div>
            <div class="flex items-center gap-6 bg-white px-6 py-3 rounded-2xl shadow-sm border border-gray-100">
                <div class="text-right">
                    <p class="text-[10px] font-black text-gray-400 uppercase leading-none mb-1">Estado del Sistema</p>
                    <p class="text-xs font-bold text-green-500 flex items-center justify-end">
                        <span class="relative flex h-2 w-2 mr-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        Sincronizado
                    </p>
                </div>
            </div>
        </div>
        <!-- GRÁFICOS Y LISTADO DE ASISTENCIAS -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
            <div class="lg:col-span-2 flex flex-col gap-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- GRÁFICO RESUMEN CON DESGLOSE DE AUSENTES -->
                    <div class="bg-gray-200 p-6 rounded-3xl shadow-sm border border-gray-100 transition-all hover:shadow-md flex flex-col justify-between">
                        <div>
                            <h3 class="text-xs font-black text-gray-400 uppercase mb-4 tracking-widest flex items-center justify-between">
                                <span class="flex items-center">
                                    <span class="w-2 h-4 bg-indigo-600 rounded-full mr-2"></span> Resumen de Hoy
                                </span>
                                <!-- Indicador rápido de ausencias en rojo -->
                                @if (count($listaAusentes) > 0)
                                    <button wire:click="$set('mostrarModalAusentes', true)" type="button" 
                                        class="bg-red-50 text-red-600 px-2 py-0.5 rounded-full text-[10px] font-bold normal-case">
                                        Ver {{ count($listaAusentes) }} Sin Marcar →
                                    </button>
                                @endif
                            </h3>
                            <!-- Contenedor del Gráfico Doughnut -->
                            <div wire:ignore class="relative mb-6">
                                <div style="height: 200px;">
                                    <canvas id="chartAsistencia"></canvas>
                                </div>
                                <!-- Texto centralizado dentro de la dona -->
                                <div
                                    class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-4">
                                    <span
                                        class="text-2xl font-black text-gray-800">{{ $dataGrafico['asistencias'] }}</span>
                                    <span
                                        class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Presentes</span>
                                </div>
                            </div>
                        </div>
                        <!-- Sección de Ausentes Dinámica -->
                        {{-- 
                        <div class="border-t border-gray-100 pt-4 mt-auto">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold text-gray-500">Personal Ausente</span>
                                @if (count($listaAusentes) > 1)
                                    <button wire:click="$set('mostrarModalAusentes', true)" type="button"
                                        class="text-[11px] font-bold text-indigo-600 hover:underline">
                                        Ver todos ({{ count($listaAusentes) }}) →
                                    </button>
                                @endif
                            </div>
                            
                            @if ($listaAusentes->isEmpty())
                                <div class="flex items-center p-3 bg-green-50 rounded-xl text-green-700 gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    <span class="text-xs font-bold">¡Asistencia completa el día de hoy!</span>
                                </div>
                            @else
                                <!-- Vista previa de los primeros 3 ausentes -->
                                <div class="space-y-2">
                                    @foreach ($listaAusentes->take(1) as $ausente)
                                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded-xl border border-gray-100">
                                            <div class="flex items-center gap-3">
                                                <div class="w-7 h-7 bg-red-100 rounded-lg flex items-center justify-center text-red-600 text-xs font-black">
                                                    {{ strtoupper(substr($ausente->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-gray-700 line-clamp-1">{{ $ausente->name }}</p>
                                                    <p class="text-[10px] text-gray-400 font-medium">DNI: {{ $ausente->dni }}</p>
                                                </div>
                                            </div>
                                            <span class="w-1.5 h-1.5 bg-red-400 rounded-full animate-pulse mr-2"></span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            
                        </div>
                        --}}
                    </div>
                    <!-- CUMPLIMINETO POR ROL -->
                    <div class="bg-gray-200 p-6 rounded-3xl shadow-sm border border-gray-100">
                        <h3 class="text-xs font-black text-gray-400 uppercase mb-4 flex items-center">
                            <span class="w-2 h-4 bg-indigo-600 rounded-full mr-2"></span> Cumplimiento por Rol
                        </h3>
                        <div class="space-y-5 overflow-y-auto h-[200px] pr-2">
                            @foreach ($asistenciaPorRol as $rol)
                                <div>
                                    <div class="flex justify-between text-[10px] font-bold uppercase mb-1.5">
                                        <span class="text-gray-600">{{ $rol['nombre'] }}</span>
                                        <span class="text-indigo-600">{{ $rol['porcentaje'] }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="{{ $rol['color'] }} h-2 rounded-full transition-all duration-1000"
                                            style="width: {{ $rol['porcentaje'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!-- TABLA DETALLE DE MARCACIONES -->
                <div class="bg-gray-200 rounded-3xl shadow-xl border border-gray-200 overflow-hidden flex-1">
                    <div class="p-6 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                        <h3 class="text-xs font-black text-gray-800 uppercase tracking-widest italic">Detalle de
                            Marcaciones</h3>
                        <x-jet-input wire:model="search" placeholder="Buscar colaborador..."
                            class="text-xs border-gray-200 rounded-xl focus:ring-indigo-500 w-64" />
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead
                                class="bg-slate-600 text-[10px] font-black text-white uppercase border-b border-gray-100">
                                <tr>
                                    <th class="px-4 py-5">Colaborador</th>
                                    <th class="px-2 py-5 text-center">Entrada</th>
                                    <th class="px-4 py-5 text-center">Salida</th>
                                    <th class="px-2 py-5 text-center">Estado</th>
                                    <th class="px-2 py-5 text-right">Tardanza</th>
                                    <th class="px-4 py-5 text-center">Horario</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($asistencias as $asistencia)
                                    <tr class="hover:bg-indigo-50/30 transition-colors group">
                                        <td class="px-4 py-4">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-10 h-10 rounded-xl bg-gray-900 flex items-center justify-center text-white font-black text-sm group-hover:scale-110 transition-transform shadow-lg">
                                                    {{ substr($asistencia->usuario->name, 0, 1) }}
                                                </div>
                                                <div class="ml-4">
                                                    <p
                                                        class="text-xs font-black text-gray-800 uppercase tracking-tighter">
                                                        {{ $asistencia->usuario->name }}</p>
                                                    <p class="text-[10px] text-gray-400 font-bold uppercase">
                                                        {{ $asistencia->usuario->dni }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-2 py-4 text-center text-xs font-bold text-gray-600">
                                            <span class="bg-gray-100 px-3 py-1 rounded-lg">
                                                {{ $asistencia->hora_entrada ? $asistencia->hora_entrada->format('H:i') : '--:--' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center text-xs font-bold text-gray-600">
                                            <span class="bg-gray-100 px-3 py-1 rounded-lg">
                                                {{ $asistencia->hora_salida ? $asistencia->hora_salida->format('H:i') : 'Planta' }}
                                            </span>
                                        </td>
                                        <td class="px-2 py-4 text-center">
                                            <span
                                                class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest shadow-sm
                                                {{ $asistencia->estado == 'Puntual' ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                                {{ $asistencia->estado }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-2 py-4 text-right text-xs font-black {{ $asistencia->minutos_tardanza > 0 ? 'text-red-600' : 'text-gray-300' }}">
                                            {{ $asistencia->minutos_tardanza }} <span class="text-[9px]">MIN</span>
                                        </td>
                                        <!-- Reemplaza la celda del Horario por esta versión limpia -->
                                        <td class="px-4 py-4 text-center text-xs font-bold text-gray-600">
                                            @php
                                                // Extraemos la asignación y el primer detalle (que ya viene filtrado por el día de hoy)
                                                $asignacion = $asistencia->usuario->horariosAsignados->first();
                                                $detalleHoy = $asignacion?->horario?->detalles?->first();
                                            @endphp

                                            @if ($detalleHoy)
                                                <span
                                                    class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-lg border border-indigo-100 text-[11px] uppercase tracking-tight">
                                                    {{ Carbon\Carbon::parse($detalleHoy->hora_entrada)->format('H:i') }}
                                                    -
                                                    {{ Carbon\Carbon::parse($detalleHoy->hora_salida)->format('H:i') }}
                                                </span>
                                            @else
                                                <span
                                                    class="bg-amber-50 text-amber-700 px-3 py-1 rounded-lg border border-amber-100 text-[10px] font-bold uppercase">
                                                    Sin horario hoy
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- ACTIVIDAD RECIENTE Y ALERTA -->
            <div class="h-full">
                <div
                    class="bg-gray-200 p-8 rounded-3xl shadow-xl border border-gray-200 relative overflow-hidden h-full flex flex-col">
                    <h3 class="text-xs font-black text-gray-800 uppercase mb-8 tracking-[0.2em] flex items-center">
                        <span class="w-2 h-4 bg-indigo-600 rounded-full mr-2"></span> Actividad Reciente
                    </h3>
                    <div class="space-y-8 relative flex-1">
                        <div class="absolute left-2 top-0 bottom-0 w-0.5 bg-gray-100"></div>
                        @forelse($actividadReciente as $item)
                            @php
                                $esSalida = !is_null($item->hora_salida);
                            @endphp
                            <div class="flex items-start gap-4 relative z-10">
                                <div
                                    class="w-4 h-4 rounded-full border-4 {{ $esSalida ? 'border-orange-200 bg-orange-500' : 'border-indigo-100 bg-indigo-600' }}">
                                </div>
                                <div class="flex-1 -mt-1">
                                    <p class="text-[11px] font-bold text-gray-800 uppercase leading-none">
                                        {{ $item->usuario->name }}</p>
                                    <p class="text-[9px] text-gray-400 mt-1 uppercase">
                                        {{ $esSalida ? 'Marcó Salida' : 'Marcó Entrada' }} •
                                        <span class="font-bold text-gray-600">
                                            {{ $esSalida ? $item->hora_salida->format('H:i A') : $item->hora_entrada->format('H:i A') }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-xs text-gray-400 py-10">Sin actividad</p>
                        @endforelse
                    </div>

                    <div class="mt-8 p-4 bg-indigo-600 rounded-2xl text-white">
                        <p class="text-[9px] font-black uppercase opacity-50 tracking-tighter">Resumen Crítico</p>
                        <p class="text-sm font-bold mt-1 text-white">{{ $stats['tardanzas'] }} Tardanzas detectadas</p>
                        <div class="mt-3 w-full bg-white/10 h-1 rounded-full">
                            <div class="bg-white h-1 rounded-full"
                                style="width: {{ $stats['total'] > 0 ? ($stats['tardanzas'] / $stats['total']) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modal para ver ausentes -->
    <x-jet-dialog-modal wire:model.live="mostrarModalAusentes" maxWidth="md">
        <x-slot name="title">
            <div>
                <h3 class="text-sm font-black uppercase tracking-tight">Personal No Registrado</h3>
                <p class="text-[11px] font-bold uppercase mt-0.5">Listado de faltas de hoy</p>
            </div>
        </x-slot>
        <x-slot name="content">
            <div class="space-y-2 mb-6">
                @foreach ($listaAusentes as $ausente)
                    <div class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-2xl border border-gray-100 transition-colors">
                        <div class="w-9 h-9 bg-red-50 rounded-xl flex items-center justify-center text-red-500 text-xs font-black border border-red-100">
                            {{ strtoupper(substr($ausente->name, 0, 2)) }}
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-gray-800">{{ $ausente->name }}</p>
                            <p class="text-[10px] text-gray-400 font-semibold uppercase">Documento: {{ $ausente->dni }}</p>
                        </div>
                        <span class="text-[10px] bg-red-50 text-red-600 px-2.5 py-1 rounded-lg font-bold border border-red-100">Falta</span>
                    </div>
                @endforeach
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('mostrarModalAusentes', false)"
                class="rounded-xl text-xs font-bold">
                Cerrar
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var asistenciaChart;

        function initChart() {
            const ctx = document.getElementById('chartAsistencia').getContext('2d');
            asistenciaChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Presentes', 'Ausentes'],
                    datasets: [{
                        data: [{{ $dataGrafico['asistencias'] }}, {{ $dataGrafico['ausencias'] }}],
                        backgroundColor: ['#4f46e5',
                        '#fee2e2'], // #fee2e2 es un rojo suave muy elegante para ausencias
                        hoverOffset: 4,
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '75%',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        document.addEventListener('livewire:load', () => initChart());
        window.addEventListener('contentChanged', () => {
            asistenciaChart.data.datasets[0].data = [@this.dataGrafico.asistencias, @this.dataGrafico.ausencias];
            asistenciaChart.update();
        });
    </script>
</div>
