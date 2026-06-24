<div class="w-full py-12 px-4">
    <div class="max-w-7xl mx-auto">
        <!-- CONTENEDOR PRINCIPAL -->
        <div class="bg-gray-200 p-6 rounded-2xl shadow">
            <!-- TÍTULO -->
            <div class="mb-6">
                <h2 class="text-indigo-600 font-bold text-2xl flex items-center gap-2">
                    <i class="fa-solid fa-table fa-xl"></i>
                    Tabla de Gratificaciones
                </h2>
            </div>

            <!-- FILTROS Y BOTONES -->
            <div class="flex items-center gap-4 mb-6 w-full">
                <!-- MOSTRAR REGISTROS -->
                <div class="flex items-center bg-gray-50 p-2 rounded-xl shadow-sm flex-grow">
                    <span class="mr-2 text-sm">Mostrar</span>
                    <select wire:model="cant"
                        class="bg-white border border-indigo-500 rounded-lg px-2 py-1 focus:border-indigo-600 outline-none w-full">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="ml-2 text-sm">registros</span>
                </div>
                <!-- PERIODO MES -->
                <div class="flex items-center bg-gray-50 p-2 rounded-xl shadow-sm flex-grow">
                    <i class="fa-solid fa-calendar-days text-indigo-600 mr-2"></i>
                    <select wire:model="periodo_mes"
                        class="bg-white border border-indigo-500 rounded-lg px-3 py-1 focus:border-indigo-600 outline-none w-full">
                        <option value="">Mes</option>
                        <option value="7">Julio</option>
                        <option value="12">Diciembre</option>
                    </select>
                </div>
                <!-- PERIODO AÑO -->
                <div class="flex items-center bg-gray-50 p-2 rounded-xl shadow-sm flex-grow">
                    <i class="fa-solid fa-calendar-alt text-indigo-600 mr-2"></i>
                    <select wire:model="periodo_anio"
                        class="bg-white border border-indigo-500 rounded-lg px-3 py-1 focus:border-indigo-600 outline-none w-full">
                        <option value="">Año</option>
                        @for ($i = now()->year; $i >= now()->year - 5; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <!-- BUSCADOR -->
                <div class="flex items-center bg-gray-50 p-2 rounded-xl shadow-sm flex-grow">
                    <i class="fas fa-search text-indigo-600 mr-2"></i>
                    <input type="text" wire:model.debounce.300ms="search" placeholder="Buscar..."
                        class="bg-white border border-indigo-500 rounded-lg px-3 py-1 focus:border-indigo-600 outline-none w-full">
                </div>
                <!-- BOTÓN CREAR -->
                <button wire:click="$emit('abrirCrearGratificacion')"
                    class="bg-indigo-600 hover:bg-indigo-700 px-6 py-3 rounded-xl text-white font-semibold shadow transition flex-grow">
                    Gratificación &nbsp;<i class="fas fa-plus"></i>
                </button>

            </div>

            <!-- TABLA -->
            @if ($this->periodoSeleccionado)
                <div class="overflow-x-auto bg-white rounded-2xl shadow">
                    <table class="w-full text-sm table-auto">
                        <thead class="bg-slate-600 text-white">
                            <tr>
                                <th class="px-2 py-2">#</th>
                                <th class="px-2 py-2">Trabajadores</th>
                                <th class="px-2 py-2">F.Ingreso</th>
                                <th class="px-2 py-2">Mes</th>
                                <th class="px-2 py-2">Sueldo</th>
                                <th class="px-2 py-2">Asignacion</th>
                                <th class="px-2 py-2">Monto</th>
                                <th class="px-2 py-2">Bonificacion</th>
                                <th class="px-2 py-2">N° Tarjeta</th>
                                <th class="px-2 py-2">Monto Final</th>
                                <th class="px-2 py-2">Pagado</th>
                                <th class="px-2 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($gratificaciones as $i => $detalle)
                                <tr class="border-b hover:bg-gray-100">
                                    <td class="px-2 py-2 text-center">{{ $i + 1 }}</td>
                                    <td class="px-2 py-2">
                                        {{ $detalle->contrato->empleado->name }}
                                    </td>
                                    <td class="px-2 py-2 text-center text-xs text-gray-500">
                                        {{ $detalle->contrato->fechaInicio }}
                                    </td>
                                    <td class="px-2 py-2 text-center text-xs text-gray-500">
                                        {{ $detalle->meses_completos }}
                                    </td>
                                    <td class="px-2 py-2 text-center text-xs text-gray-500">
                                        S/ {{ number_format($detalle->sueldo, 2) }}
                                    </td>
                                    <td class="px-2 py-2 text-center text-xs text-gray-500">
                                        S/ {{ number_format($detalle->asignacion, 2) }}
                                    </td>
                                    <td class="px-2 py-2 text-center text-xs font-bold text-indigo-700">
                                        S/ {{ number_format($detalle->monto, 2) }}
                                    </td>
                                    <td class="px-2 py-2 text-center text-xs text-gray-500">
                                        S/ {{ number_format($detalle->bonificacion, 2) }}
                                    </td>
                                    <td class="px-2 py-2 text-center text-xs text-gray-500">
                                        {{ $detalle->numero_cuenta }}
                                    </td>
                                    <td class="px-2 py-2 text-center font-bold text-green-600">
                                        S/ {{ number_format($detalle->monto_final, 2) }}
                                    </td>
                                    <td class="px-2 py-2 text-center">
                                        <input type="checkbox" wire:click="togglePago({{ $detalle->id }})"
                                            @checked($detalle->pagado)
                                            class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    </td>
                                    <td class="text-center">
                                        <div class="flex justify-center items-center space-x-2">
                                            <button wire:click="edit({{ $detalle->id }})"
                                                class="group flex py-2 px-2 items-center rounded-md bg-blue-300 text-white hover:bg-blue-400 hover:animate-pulse">
                                                <i class="fa fa-pencil"></i>
                                            </button>

                                            <button wire:click="$emit('abrirGratificaciones', {{ $detalle->id }})"
                                                class="group flex py-2 px-2 items-center rounded-md bg-yellow-300 text-white hover:bg-yellow-400 hover:animate-pulse">
                                                <i class="fa-solid fa-folder"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="border-b hover:bg-gray-100">
                                <td colspan="9" class="font-bold text-right pr-2">Total:</td>
                                <td class="px-3 py-3 text-center font-bold text-green-600">
                                    S/ {{ number_format($total_monto_final, 2) }}
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-4 text-center font-bold bg-indigo-200 rounded-md">
                    Seleccione un periodo para ver los detalles.
                </div>
            @endif
            <!-- Componente crear gratificación -->
            @livewire('gratificaciones.crear-gratificacion')

            <!-- Componente archivos de gratificación -->
            @livewire('gratificaciones-archivos')

        </div>
    </div>
</div>
