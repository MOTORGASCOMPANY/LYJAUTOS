<div>
    <button data-tooltip-target="tooltip-dark" type="button" wire:click="$set('addDocument',true)"
        class="group flex py-4 px-4 text-center rounded-md bg-blue-300 font-bold text-white cursor-pointer hover:bg-blue-400 hover:animate-pulse">
        <i class="fas fa-folder-plus"></i>
        <span
            class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute left-1/2-translate-x-1/2 translate-y-full opacity-0 m-4 mx-auto z-100">
            Agregar Documentos
        </span>
    </button>

    <x-jet-dialog-modal wire:model="addDocument">
        <x-slot name="title">
            <h1 class="text-xl font-bold">Agregar documento del Empleado {{ $empleado->empleado->name }}</h1>
        </x-slot>
        <x-slot name="content">
            <div wire:key="documento-formulario">
                <div class="mb-4">
                    <x-jet-label value="tipo de documento:" />
                    <select wire:model="tipoSel" class="bg-gray-50 border-indigo-500 rounded-md outline-none w-full">
                        <option value="">Seleccione</option>
                        @foreach ($tiposDisponibles as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->nombreTipo }}</option>
                        @endforeach
                    </select>
                    <x-jet-input-error for="tipoSel" />
                </div>

                {{-- LÓGICA PARA CONTRATO (ID 4) --}}
                @if ($tipoSel == 4)
                    <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-lg animate-fade-in-down">
                        <div class="flex items-center mb-2 text-indigo-800 font-bold">
                            <i class="fas fa-pen-nib mr-2"></i> Firma Digital Automática
                        </div>
                        <p class="text-xs text-indigo-600 mb-4">
                            Al ser un Contrato, el sistema generará el documento y estampará tu firma registrada
                            automáticamente.
                        </p>
                        <label for="acepto" class="flex items-start cursor-pointer">
                            <div class="flex items-center h-5">
                                <x-jet-checkbox id="acepto" wire:model="acepto" class="h-5 w-5 text-indigo-600" />
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="font-medium text-indigo-900">
                                    "Autorizo el estampado de mi firma digital en este contrato."
                                </span>
                            </div>
                        </label>
                        <x-jet-input-error for="acepto" class="mt-2" />
                    </div>
                @else
                    <div class="mb-4" wire:key="archivo-documento">
                        <x-jet-label value="Archivo (PDF):" class="font-bold" />
                        <x-file-pond name="documento" id="documento" wire:model="documento"
                            acceptedFileTypes="['application/pdf']" aceptaVarios="false" />
                        <x-jet-input-error for="documento" />
                    </div>
                @endif
            </div>

        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('addDocument',false)" class="mx-2">
                Cancelar
            </x-jet-secondary-button>
            <x-jet-button loading:attribute="disabled" wire:click="agregarDocumento" wire:target="agregarDocumento">
                Guardar
            </x-jet-button>
        </x-slot>

    </x-jet-dialog-modal>
</div>
