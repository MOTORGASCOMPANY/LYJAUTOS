<div class="mb-4">
    <button wire:click="$set('open',true)"
        class="bg-indigo-600 px-6 py-4 rounded-md text-white font-semibold tracking-wide cursor-pointer">
        Agregar Documento
    </button>

    <x-jet-dialog-modal wire:model="open">
        <x-slot name="title">
            <h1 class="text-xl font-bold">Subir Documento Contable</h1>
        </x-slot>
        <x-slot name="content">
            <div class="space-y-6">
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <x-jet-label value="Tipo de Operación" class="font-bold text-gray-700" />
                            <select wire:model="tipo" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                                <option value="compra">Compra (Imagen/PDF Individual)</option>
                                <option value="venta">Venta (PDF Listado CPE)</option>
                            </select>
                        </div>
                        <div>
                            <x-jet-label value="Seleccionar Archivo" class="font-bold text-gray-700" />
                            <div wire:loading wire:target="archivo" class="text-sm text-indigo-600 font-bold animate-pulse mb-2">
                                🤖 Analizando con IA... extrayendo datos...
                            </div>
                            <x-file-pond name="archivo" id="archivo" wire:model="archivo"
                                acceptedFileTypes="['application/pdf', 'image/jpeg', 'image/png']">
                            </x-file-pond>
                        </div>
                    </div>
                </div>

                <div class="mt-2">
                    @if (count($listaVentas) > 0 && $tipo == 'venta')
                        <div class="border rounded-lg overflow-hidden shadow-inner bg-white">
                            <div class="bg-gray-800 text-white px-4 py-2 text-sm font-bold flex justify-between">
                                <span>Detalle del Listado Extraído</span>
                                <span># Docs: {{ count($listaVentas) }}</span>
                            </div>
                            <div class="overflow-y-auto max-h-80">
                                <table class="min-w-full divide-y divide-gray-200 text-xs">
                                    <thead class="bg-gray-100 sticky top-0">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-gray-600">Fecha/Número</th>
                                            <th class="px-4 py-2 text-left text-gray-600">RUC / Proveedor</th>
                                            <th class="px-4 py-2 text-right text-gray-600">IGV (18%)</th>
                                            <th class="px-4 py-2 text-right text-gray-600">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($listaVentas as $v)
                                            <tr class="hover:bg-indigo-50 transition">
                                                <td class="px-4 py-2">
                                                    <div class="text-gray-500">{{ $v['fecha'] ?? '---' }}</div>
                                                    <div class="font-mono font-bold">{{ $v['numero'] ?? 'S/N' }}</div>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <div class="font-bold text-gray-800">{{ $v['ruc'] ?? 'N/A' }}</div>
                                                    <div class="text-gray-500 truncate w-56">
                                                        {{ $v['proveedor'] ?? '---' }}</div>
                                                </td>
                                                <td class="px-4 py-2 text-right text-gray-500">
                                                    S/ {{ number_format($v['igv'], 2) }}
                                                </td>
                                                <td class="px-4 py-2 text-right">
                                                    <span class="text-indigo-700 font-bold text-sm">
                                                        S/ {{ number_format((float) ($v['total'] ?? 0), 2) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="bg-indigo-50 p-5 rounded-lg border-2 border-indigo-100">
                            <h2
                                class="text-xs font-black text-indigo-800 mb-4 uppercase tracking-widest border-b border-indigo-200 pb-2">
                                Datos de Comprobante Individual
                            </h2>
                            <div class="grid grid-cols-1 gap-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <x-jet-label value="Número CPE" class="text-xs text-gray-600" />
                                        <x-jet-input wire:model="numero" type="text"
                                            class="w-full text-sm shadow-sm" />
                                    </div>
                                    <div>
                                        <x-jet-label value="Fecha Emisión" class="text-xs text-gray-600" />
                                        <x-jet-input wire:model="fecha_emision" type="date"
                                            class="w-full text-sm shadow-sm" />
                                    </div>
                                </div>
                                <div>
                                    <x-jet-label value="RUC Receptor/Emisor" class="text-xs text-gray-600" />
                                    <x-jet-input wire:model="ruc" type="text"
                                        class="w-full text-sm font-bold shadow-sm" />
                                </div>
                                <div>
                                    <x-jet-label value="Razón Social / Proveedor" class="text-xs text-gray-600" />
                                    <x-jet-input wire:model="proveedor" type="text"
                                        class="w-full text-sm shadow-sm" />
                                </div>
                                <div class="grid grid-cols-2 gap-4 pt-2">
                                    <div class="bg-white p-2 rounded border border-indigo-100">
                                        <x-jet-label value="IGV" class="text-xs text-gray-500" />
                                        <x-jet-input wire:model="igv" type="number" step="0.01"
                                            class="w-full border-none p-0 text-sm focus:ring-0" />
                                    </div>
                                    <div class="bg-indigo-600 p-2 rounded shadow-md">
                                        <x-jet-label value="Monto Total" class="text-xs text-white opacity-80" />
                                        <x-jet-input wire:model="monto_total" type="number" step="0.01"
                                            class="w-full border-none p-0 text-white bg-transparent font-bold text-lg focus:ring-0" />
                                    </div>
                                </div>
                                @if ($tipo == 'compra')
                                    <div class="pt-2">
                                        <x-jet-label value="Descripción / Notas" class="text-xs text-gray-600" />
                                        <textarea wire:model="descripcion" class="w-full border-gray-300 rounded-md text-sm shadow-sm" rows="2"></textarea>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('open',false)" class="mx-2">
                Cancelar
            </x-jet-secondary-button>
            @if (count($listaVentas) > 0 && $tipo == 'venta')
                <x-jet-button wire:click="guardarMasivo" class="bg-green-600 hover:bg-green-700">
                    Guardar {{ count($listaVentas) }} Ventas
                </x-jet-button>
            @else
                <x-jet-button wire:click="guardar" wire:loading.attr="disabled">
                    Guardar Individual
                </x-jet-button>
            @endif
        </x-slot>
    </x-jet-dialog-modal>
</div>
