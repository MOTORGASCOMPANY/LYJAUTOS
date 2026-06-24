<div class="p-6 pt-12 bg-gray-50 min-h-screen font-sans">
    <div class="max-w-7xl mx-auto">
        <div class="bg-gray-200 p-6 rounded-3xl shadow-xl border border-gray-200 mb-8">
            <!-- TÍTULO Y ACCIONES -->
            <div class="flex flex-col md:flex-row items-center justify-between gap-2 mb-6">
                <!-- TÍTULO -->
                <div>
                    <h1 class="text-2xl font-bold text-indigo-600 tracking-tight">Reportes de Asistencia</h1>
                    <span class="text-xs">Motor Gas — Auditoría de Personal</span>
                </div>
                <!-- ACCIONES -->
                <div class="flex gap-3">
                    <x-jet-button wire:click="exportExcel" wire:loading.attr="disabled" class="bg-green-600 hover:bg-green-700 active:bg-green-800 shadow-green-100">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Excel
                    </x-jet-button>
                    <x-jet-button wire:click="exportPDF" class="bg-red-600 hover:bg-red-700 active:bg-red-800 shadow-red-100">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        PDF
                    </x-jet-button>
                </div>
            </div>
            <!-- FILTROS -->        
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6">
                <!-- Fecha, desde -->
                <div class="flex items-center bg-gray-50 p-2 rounded-md">
                    <span>Desde: </span>
                    <x-date-picker wire:model="fechaInicio" placeholder="Fecha de inicio"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                </div>
                <!-- Fecha, hasta -->
                <div class="flex items-center bg-gray-50 p-2 rounded-md">
                    <span>Hasta:</span>
                    <x-date-picker wire:model="fechaFin" placeholder="Fecha de Fin"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                </div>
                <!-- Estado -->
                <div class="flex items-center bg-gray-50 p-2 rounded-md">
                    <span>Estado:</span>
                    <select wire:model="estado"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate">
                        <option value="">Todos</option>
                        <option value="Puntual">Puntual</option>
                        <option value="Tardanza">Tardanza</option>
                        <option value="Incompleto">Incompleto</option>
                    </select>
                </div>
                <!-- Buscar -->
                {{-- 
                <div class="flex items-center bg-gray-50 p-2 rounded-md sm:col-span-2 lg:col-span-3">
                    <span>Buscar:</span>
                    <input type="text" wire:model.debounce.500ms="search" placeholder="DNI o Nombre..."
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                </div>
                --}}
                @unless(auth()->user()->hasRole('inspector'))
                    <div class="flex items-center bg-gray-50 p-2 rounded-md sm:col-span-2 lg:col-span-3">
                        <span>Buscar:</span>
                        <input type="text" wire:model.debounce.500ms="search" placeholder="DNI o Nombre..."
                            class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                    </div>
                @else
                    <div class="flex items-center bg-indigo-50 p-2 rounded-md sm:col-span-2 lg:col-span-3 border border-indigo-100">
                        <span class="text-indigo-600 font-bold text-xs uppercase px-2">
                            Mostrando mis asistencias personales
                        </span>
                    </div>
                @endunless
            </div>
        </div>

        <!-- TABLA DE REPORTES -->
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-600 text-[10px] font-black text-white uppercase border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-5">Fecha</th>
                            <th class="px-6 py-5">Colaborador</th>
                            <th class="px-6 py-5 text-center">Entrada / Salida</th>
                            <th class="px-6 py-5 text-center">Estado</th>
                            <th class="px-6 py-5 text-center">Tardanza</th>
                            <th class="px-6 py-5 text-center">Trabajado</th>
                            <th class="px-6 py-5 text-right">H. Extras</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($reportes as $item)
                            <tr class="hover:bg-indigo-50/20 transition-colors">
                                <td class="px-6 py-5 text-xs font-black text-gray-700">
                                    {{ $item->fecha->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center">
                                        <div class="ml-3">
                                            <p class="text-xs font-black text-gray-800 uppercase leading-none">
                                                {{ $item->usuario->name }}
                                            </p>
                                            <p class="text-[9px] text-gray-400 font-bold mt-1">
                                                {{ $item->usuario->dni }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="text-xs font-bold text-gray-600">
                                        <span
                                            class="text-indigo-600">{{ $item->hora_entrada ? $item->hora_entrada->format('H:i') : '--:--' }}</span>
                                        <span class="mx-1 text-gray-300">|</span>
                                        <span
                                            class="text-orange-600">{{ $item->hora_salida ? $item->hora_salida->format('H:i') : '--:--' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span
                                        class="px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-tighter
                                        {{ $item->estado == 'Puntual' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                        {{ $item->estado }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-5 text-center text-xs font-black {{ $item->minutos_tardanza > 0 ? 'text-red-600' : 'text-gray-300' }}">
                                    {{ $item->minutos_tardanza }} min
                                </td>
                                <td class="px-6 py-5 text-center text-xs font-bold text-gray-500">
                                    {{ $item->minutos_trabajados ? number_format($item->minutos_trabajados / 60, 2) : '0.00' }}
                                    hrs
                                </td>
                                <td class="px-6 py-5 text-right text-xs font-black text-indigo-700">
                                    {{ $item->horas_extras_minutos > 0 ? number_format($item->horas_extras_minutos / 60, 2) . ' hrs' : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7"
                                    class="px-6 py-10 text-center text-gray-400 text-xs italic uppercase">
                                    No se encontraron registros para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-6 bg-gray-50/50 border-t border-gray-100">
                {{ $reportes->links() }}
            </div>
        </div>
    </div>
</div>
