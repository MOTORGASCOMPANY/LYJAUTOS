<div class="p-6 pt-12 bg-gray-100 min-h-screen font-sans">
    <div class="max-w-7xl mx-auto">        
        <div class="flex flex-col md:flex-row items-center justify-between gap-2 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-indigo-600 tracking-tight">Gestión de Horarios</h1>
                <span class="text-xs">Motor Gas — Configuración Operativa</span>
            </div>
            <x-jet-button wire:click="create" class="bg-indigo-600">
                + Nuevo Horario
            </x-jet-button>
        </div>

        <!-- Tabla de Horarios -->
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
            <table class="w-full text-left">
                <thead class="bg-slate-600 text-[10px] font-black text-white uppercase">
                    <tr>
                        <th class="px-6 py-4">Nombre</th>
                        <th class="px-6 py-4">Descripción</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($horarios as $h)
                    <tr class="hover:bg-indigo-50/30 transition">
                        <td class="px-6 py-4 font-black text-gray-700 text-sm">{{ $h->nombre }}</td>
                        <td class="px-6 py-4 text-xs text-gray-500">{{ $h->descripcion }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 rounded-full text-[9px] font-black uppercase {{ $h->activo ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                {{ $h->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <x-jet-button wire:click="edit({{ $h->id }})" class="text-[10px] bg-gray-800">
                                Editar
                            </x-jet-button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Modal de Configuración de Horario -->
        <x-jet-dialog-modal wire:model="isModalOpen" maxWidth="4xl">
            <x-slot name="title">Configuración de Horario</x-slot>
            <x-slot name="content">
                <!-- Horario -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <x-jet-label value="Nombre del Horario" />
                        <x-jet-input type="text" class="w-full" wire:model.defer="nombre" placeholder="Ej: Administrativo, Operarios..." />
                        <x-jet-input-error for="nombre" />
                    </div>
                    <div>
                        <x-jet-label value="Descripción Corta" />
                        <x-jet-input type="text" class="w-full" wire:model.defer="descripcion" />
                    </div>
                </div>
                <!-- Horario detallado por día -->
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-600 text-white">
                            <tr>
                                <th class="p-2 rounded-tl-lg">Día</th>
                                <th class="p-2 text-center">Lab.</th>
                                <th class="p-2">Entrada</th>
                                <th class="p-2">Salida</th>
                                <th class="p-2">Tolerancia</th>
                                <th class="p-2 rounded-tr-lg">Descanso (Ini/Fin)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-gray-50">
                            @foreach($detalles as $index => $det)
                            <tr>
                                <td class="p-2 font-bold">{{ $det['nombre_dia'] }}</td>
                                <td class="p-2 text-center">
                                    <input type="checkbox" wire:model="detalles.{{$index}}.es_laborable" class="rounded text-indigo-600">
                                </td>
                                @if($detalles[$index]['es_laborable'])
                                    <td class="p-1"><x-jet-input type="time" class="p-1 text-xs w-full" wire:model.defer="detalles.{{$index}}.hora_entrada" /></td>
                                    <td class="p-1"><x-jet-input type="time" class="p-1 text-xs w-full" wire:model.defer="detalles.{{$index}}.hora_salida" /></td>
                                    <td class="p-1"><x-jet-input type="number" class="p-1 text-xs w-full" wire:model.defer="detalles.{{$index}}.tolerancia_tardanza" /></td>
                                    <td class="p-1 flex gap-1">
                                        <x-jet-input type="time" class="p-1 text-xs w-full" wire:model.defer="detalles.{{$index}}.hora_descanso_inicio" />
                                        <x-jet-input type="time" class="p-1 text-xs w-full" wire:model.defer="detalles.{{$index}}.hora_descanso_fin" />
                                    </td>
                                @else
                                    <td colspan="4" class="p-2 text-center text-gray-400 italic">No laborable / Descanso</td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-slot>
            <x-slot name="footer">
                <x-jet-secondary-button wire:click="$set('isModalOpen',false)" class="mx-2">
                    Cancelar
                </x-jet-secondary-button>
                <x-jet-button loading:attribute="disabled" wire:click="save" wire:target="save">
                    Guardar Horario
                </x-jet-button>
            </x-slot>
        </x-jet-dialog-modal>
    </div>
</div>