<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-8 border-t-4 border-indigo-500">
            <div class="flex items-center mb-6 pb-2 border-b border-gray-100">
                <i class="fas fa-calendar-alt text-indigo-500 mr-2 text-xl"></i>
                <h3 class="text-lg font-bold text-gray-700 uppercase tracking-tight">Vincular Horario a Personal</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- TRABAJADOR -->
                <div class="col-span-1">
                    <x-jet-label class="text-xs font-semibold text-gray-500" value="1. TRABAJADOR" />
                    <select wire:model.defer="user_id" class="mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        <option value="">Seleccione un trabajador</option>
                        @foreach($usuarios as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <x-jet-input-error for="user_id" />
                </div>
                <!-- HORARIO -->
                <div class="col-span-1">
                    <x-jet-label class="text-xs font-semibold text-gray-500" value="2. HORARIO MAESTRO" />
                    <select wire:model.defer="horario_id" class="mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        <option value="">Seleccione horario</option>
                        @foreach($horarios as $horario)
                            <option value="{{ $horario->id }}">{{ $horario->nombre }}</option>
                        @endforeach
                    </select>
                    <x-jet-input-error for="horario_id" />
                </div>
                <!-- FECHA DE INICIO -->
                <div class="col-span-1">
                    <x-jet-label class="text-xs font-semibold text-gray-500" value="3. FECHA DE INICIO" />
                    <x-jet-input type="date" class="mt-1 w-full text-sm" wire:model.defer="fecha_inicio" />
                    <x-jet-input-error for="fecha_inicio" />
                </div>
            </div>

            <div class="mt-8 flex justify-end items-center space-x-4">
                {{-- 
                <x-jet-action-message class="text-green-600 font-medium" on="saved">
                    Actualizado.
                </x-jet-action-message>
                --}}
                
                <button wire:click="guardarAsignacion" 
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-6 py-2.5 bg-indigo-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition shadow-md">
                    <span wire:loading.remove wire:target="guardarAsignacion">Vincular Horario</span>
                    <span wire:loading wire:target="guardarAsignacion italic">Procesando...</span>
                </button>
            </div>
        </div>

        <div class="bg-white shadow-xl sm:rounded-lg overflow-hidden border border-gray-100">
            <div class="bg-slate-600 px-6 py-4 border-b border-slate-600">
                <h3 class="text-sm font-bold text-white uppercase">Horarios Vigentes</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-white uppercase bg-slate-600">
                        <tr>
                            <th class="px-6 py-4">Colaborador</th>
                            <th class="px-6 py-4">Horario Asignado</th>
                            <th class="px-6 py-4 text-center">Fecha Inicio</th>
                            <th class="px-6 py-4 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($horariosAsignados as $item)
                            <tr class="hover:bg-indigo-50/30 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $item->usuario->name }}
                                    <p class="text-[10px] text-gray-400 font-normal tracking-widest">{{ $item->usuario->dni }}</p>
                                </td>
                                <td class="px-6 py-4 italic">{{ $item->horario->nombre }}</td>
                                <td class="px-6 py-4 text-center">{{ \Carbon\Carbon::parse($item->fecha_inicio)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="w-2 h-2 mr-1.5 bg-green-500 rounded-full"></span>
                                        Activo
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">No hay registros encontrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>