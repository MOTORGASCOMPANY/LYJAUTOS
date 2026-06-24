<div class="flex box-border">
    <div class="container mx-auto py-12">
        <div class="bg-gray-200  p-8 rounded-xl w-full">
            <div class=" items-center md:block sm:block">
                <!-- Titulo y contadores -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
                    <div>
                        <h2 class="text-indigo-900 font-bold text-3xl">
                            Facturas Contabilidad
                        </h2>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
                        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-indigo-500 min-w-[180px]">
                            <p class="text-sm text-gray-500">Total IGV</p>
                            <p class="text-xl font-bold text-indigo-700 whitespace-nowrap">
                                S/. {{ number_format($totalIgv ?? 0, 2) }}
                            </p>
                        </div>
                        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-green-500 min-w-[180px]">
                            <p class="text-sm text-gray-500">Monto Total</p>
                            <p class="text-xl font-bold text-green-700 whitespace-nowrap">
                                S/. {{ number_format($totalMonto ?? 0, 2) }}
                            </p>
                        </div>
                    </div>
                </div>
                <!-- Filtros y acciones -->
                <div class="w-full flex flex-wrap items-center gap-3 mb-4">                    
                    <!-- Cantidad de entradas -->
                    <div class="flex bg-gray-50 items-center p-2 rounded-md w-full sm:w-auto">
                        <span>Mostrar</span>
                        <select wire:model="cant" class="bg-gray-50 mx-1 border-indigo-500 rounded-md outline-none block">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>Entradas</span>
                    </div>
                    <!-- Filtro tipo -->
                    <div class="flex bg-gray-50 items-center p-2 rounded-md w-full sm:w-auto">
                        <span class="mr-1">Tipo:</span>
                        <select wire:model="filtro_tipo" class="bg-gray-50 mx-1 border-indigo-500 rounded-md outline-none block">
                            <option value="todos">Todos</option>
                            <option value="compra">Compra</option>
                            <option value="venta">Venta</option>
                        </select>
                    </div>
                    <!-- Filtro fecha desde -->
                    <div class="flex bg-gray-50 items-center p-2 w-full sm:w-48 rounded-md">
                        <span class="whitespace-nowrap">Desde: </span>
                        <x-date-picker wire:model="fecIni" placeholder="Fecha de inicio"
                            class="bg-gray-50 ml-1 border-indigo-500 rounded-md outline-none block w-full truncate" />
                    </div>
                    <!-- Filtro fecha hasta -->
                    <div class="flex bg-gray-50 items-center p-2 w-full sm:w-48 rounded-md">
                        <span class="whitespace-nowrap">Hasta: </span>
                        <x-date-picker wire:model="fecFin" placeholder="Fecha de Fin"
                            class="bg-gray-50 ml-1 border-indigo-500 rounded-md outline-none block w-full truncate" />
                    </div>
                    <!-- Buscador (Flexible en pantallas grandes, ancho completo en móviles) -->
                    <div class="flex-grow flex bg-gray-50 items-center p-2 rounded-md min-w-[250px] w-full md:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                        <input class="bg-gray-50 outline-none block rounded-md border-indigo-500 w-full" type="text"
                            wire:model="search" placeholder="Buscar factura por cliente, ruc o número...">
                    </div>
                    <!-- Botón agregar -->
                    <div class="shrink-0 w-full sm:w-auto [&>div]:mb-0">
                        @livewire('contabilidad.subir-factura-contabilidad')
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm">
                <table class="min-w-full">
                    <thead class="bg-slate-600 from-indigo-100 to-indigo-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase">RUC</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase">Proveedor</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase">IGV</th>                            
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase">Monto</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase">PDF</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($facturas as $f)
                            <tr class="hover:bg-indigo-50 transition">
                                <td class="px-6 py-3">
                                    @if ($f->tipo == 'compra')
                                        <span
                                            class="px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700">
                                            COMPRA
                                        </span>
                                    @else
                                        <span
                                            class="px-3 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-700">
                                            VENTA
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3">{{ $f->ruc }}</td>
                                <td class="px-6 py-3">{{ $f->proveedor ?? 'NE'}}</td>
                                <td class="px-6 py-3">{{ $f->fecha_emision }}</td>
                                <td class="px-6 py-3">{{ $f->igv }}</td>                                
                                <td class="px-6 py-3 font-semibold text-green-700">
                                    S/. {{ number_format($f->monto_total, 2) }}
                                </td>
                                <td class="px-6 py-2 text-center">
                                    <a href="{{ Storage::url($f->ruta) }}" target="_blank"
                                        class="text-indigo-600 hover:text-indigo-900"> Ver PDF </a>
                                </td>
                                <!-- ACCIONES -->
                                <td class="px-6 py-3 text-center flex gap-3 justify-center">
                                    <!-- Editar -->
                                    <button wire:click="editar({{ $f->id }})"
                                        class="px-3 py-2 bg-blue-100 text-blue-700 rounded-xl hover:bg-blue-200 transition shadow">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <!-- Eliminar -->
                                    <button wire:click="$emit('confirmarEliminacion', {{ $f->id }})"
                                        class="px-3 py-2 bg-red-100 text-red-700 rounded-xl hover:bg-red-200 transition shadow">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                    No hay facturas registradas
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-6">
                {{ $facturas->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Editar Factura -->
    <x-jet-dialog-modal wire:model="openEditarModal">
        <x-slot name="title">
            Editar Factura
        </x-slot>
        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-jet-label value="Número" />
                    <x-jet-input wire:model.defer="numero" type="text" class="w-full" />
                    <x-jet-input-error for="numero" />
                </div>
                <div>
                    <x-jet-label value="Proveedor" />
                    <x-jet-input wire:model.defer="proveedor" type="text" class="w-full" />
                    <x-jet-input-error for="proveedor" />
                </div>
                <div>
                    <x-jet-label value="Ruc" />
                    <x-jet-input wire:model.defer="ruc" type="text" class="w-full" />
                    <x-jet-input-error for="ruc" />
                </div>
                <div>
                    <x-jet-label value="IGV" />
                    <x-jet-input wire:model.defer="igv" type="number" class="w-full" />
                    <x-jet-input-error for="igv" />
                </div>
                <div>
                    <x-jet-label value="Fecha de emisión" />
                    <x-jet-input wire:model.defer="fecha_emision" type="date" class="w-full" />
                    <x-jet-input-error for="fecha_emision" />
                </div>
                <div>
                    <x-jet-label value="Monto total" />
                    <x-jet-input wire:model.defer="monto_total" type="number" step="0.01" class="w-full" />
                    <x-jet-input-error for="monto_total" />
                </div>
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('openEditarModal', false)">
                Cancelar
            </x-jet-secondary-button>

            <x-jet-button wire:click="guardarEdicion" class="ml-2">
                Guardar
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>


</div>
