<x-jet-dialog-modal wire:model="open" maxWidth="6xl">
    <x-slot name="title">
        <span class="text-xl font-semibold">Crear Nueva Gratificación</span>
    </x-slot>
    <x-slot name="content">
        {{-- Selección de periodo --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <x-jet-label value="Periodo (Mes)" />
                <select wire:model="periodo_mes"
                    class="bg-white border border-gray-300 focus:border-indigo-500 rounded-lg px-3 py-2 w-full shadow-sm">
                    <option value="">Seleccione</option>
                    <option value="7">Julio</option>
                    <option value="12">Diciembre</option>
                </select>
            </div>
            <div>
                <x-jet-label value="Periodo (Año)" />
                <select wire:model="periodo_anio"
                    class="bg-white border border-gray-300 focus:border-indigo-500 rounded-lg px-3 py-2 w-full shadow-sm">
                    <option value="">Seleccione</option>
                    @for ($i = now()->year; $i >= now()->year - 5; $i--)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>

        {{-- Tabla empleados cargados --}}
        @if (!empty($contratos))
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full bg-gray-100 rounded-2xl text-sm shadow-md overflow-hidden">
                    <thead class="bg-slate-600 text-white">
                        <tr>
                            <th class="px-3 py-2 text-left">Empleado</th>
                            <th class="px-3 py-2 text-left">Fecha Inicio</th>
                            <th class="px-3 py-2 text-left">Sueldo</th>
                            <th class="px-3 py-2 text-center">Asignación</th>
                            <th class="px-3 py-2 text-center">Meses</th>                            
                            <th class="px-3 py-2 text-center">Monto</th>
                            <th class="px-3 py-2 text-center">Bonificacion</th>
                            <th class="px-3 py-2 text-center">Monto Final</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($contratos as $contrato)
                            <tr class="hover:bg-gray-200 transition">
                                <td class="px-4 py-3 flex items-center justify-between">
                                    {{ $contrato['empleado']['name'] ?? '' }}
                                    <button type="button" wire:click="quitarEmpleado({{ $loop->index }})"
                                        class="text-xs text-red-600 hover:text-red-800 ml-2">
                                        Quitar
                                    </button>
                                </td>
                                <td class="px-4 py-3">{{ $contrato['fechaInicio'] }}</td>
                                <td class="px-4 py-3">{{ number_format($contrato['pago'], 2) }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if ($contrato['asignacion'] > 0)
                                        <span class="text-green-700 font-semibold">113.00</span>
                                    @else
                                        <span class="text-gray-500">✘</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" min="0" max="6"
                                        wire:model.lazy="contratos.{{ $loop->index }}.meses"
                                        class="w-20 border-gray-300 rounded-lg px-2 py-1 text-sm focus:border-indigo-500 shadow-sm" readonly>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" step="0.01"
                                        wire:model.lazy="contratos.{{ $loop->index }}.monto"
                                        class="w-24 border-gray-300 rounded-lg px-2 py-1 text-sm focus:border-indigo-500 shadow-sm" readonly>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" step="0.01"
                                        wire:model.lazy="contratos.{{ $loop->index }}.bonificacion"
                                        class="w-24 border-gray-300 rounded-lg px-2 py-1 text-sm focus:border-indigo-500 shadow-sm" readonly>
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-green-700">
                                    S/. {{ number_format($contrato['monto_final'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-slot>

    <x-slot name="footer">
        <x-jet-secondary-button wire:click="$set('open',false)" class="mx-2">
            Cancelar
        </x-jet-secondary-button>
        <x-jet-button wire:click="save" wire:loading.attr="disabled" wire:target="save">
            Guardar Gratificación
        </x-jet-button>
    </x-slot>
</x-jet-dialog-modal>
