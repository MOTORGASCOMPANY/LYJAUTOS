<div class="mt-16 flex justify-center">
    <div class="bg-white p-6 rounded-xl shadow max-w-3xl w-full">
        {{-- TÍTULO --}}
        <h2 class="text-xl font-bold text-indigo-600 mb-6 flex items-center gap-2">
            <i class="fa-solid fa-folder-open"></i>
            Mis comprobantes de pago
        </h2>
        {{-- FILTRO DE PERIODO --}}
        @if(!empty($periodos))
            <div class="mb-6 flex items-center gap-3">
                <label class="text-sm font-medium text-gray-700">
                    Periodo:
                </label>

                <select wire:model="periodoSeleccionado"
                        class="border rounded-lg px-3 py-1 text-sm focus:outline-none focus:ring focus:ring-indigo-200">
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo }}">{{-- $periodo --}}
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $periodo)->translatedFormat('F Y') }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- CONTENIDO --}}
        @if($sinArchivos)
            <div class="text-center py-12 text-gray-400">
                <i class="fa-regular fa-folder-open fa-3x mb-4"></i>
                <p class="text-sm">
                    No tienes comprobantes registrados para este periodo.
                </p>
            </div>
        @else
            <div class="space-y-8">
                {{-- AGRUPADO POR TIPO DE MODELO 
                @foreach(
                    $archivos->groupBy(fn($a) => class_basename($a->archivoable_type))
                    as $tipoModelo => $archivosModelo
                )--}}
                @php
                    $grupos = $archivos
                        ->groupBy(fn($a) => class_basename($a->archivoable_type))
                        ->sortBy(fn($_, $key) => $key === 'PlanillaDetalle' ? 0 : 1);
                @endphp

                @foreach($grupos as $tipoModelo => $archivosModelo)
                    <div>
                        {{-- CABECERA PLANILLA / GRATIFICACIÓN --}}
                        <h3 class="text-sm font-semibold uppercase text-gray-600 mb-4 flex items-center gap-2">
                            @if($tipoModelo === 'PlanillaDetalle')
                                <i class="fa-solid fa-file-invoice-dollar text-indigo-500"></i>
                                Planillas
                            @else
                                <i class="fa-solid fa-gift text-green-500"></i>
                                Gratificaciones
                            @endif
                        </h3>

                        <div class="space-y-3">
                            @foreach($archivosModelo as $archivo)
                                <div class="flex items-center justify-between border rounded-lg p-3 hover:bg-gray-50 transition">
                                    {{-- INFO ARCHIVO --}}
                                    <div class="flex items-center space-x-3 min-w-0 flex-1">
                                        @if(in_array(strtolower($archivo->extension), ['jpg','jpeg','png']))
                                            <img
                                                src="{{ Storage::url($archivo->ruta) }}"
                                                class="w-14 h-14 object-cover rounded-lg"
                                            />
                                        @else
                                            <div class="w-14 h-14 flex items-center justify-center bg-gray-100 rounded-lg text-sm font-semibold text-gray-600">
                                                {{ strtoupper($archivo->extension) }}
                                            </div>
                                        @endif

                                        <div class="text-sm min-w-0 flex-1">
                                            <div class="font-medium text-gray-800 break-words line-clamp-2 leading-tight">
                                                {{ $archivo->nombre }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ ucfirst($archivo->tipo) }} ·
                                                {{ $archivo->created_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ACCIONES --}}
                                    <div class="flex flex-wrap md:flex-nowrap items-center gap-2 flex-shrink-0">
                                        <a href="{{ Storage::url($archivo->ruta) }}"
                                           target="_blank"
                                           class="px-3 py-1 border rounded text-xs hover:bg-indigo-50 transition">
                                            Ver
                                        </a>

                                        <a href="{{ Storage::url($archivo->ruta) }}"
                                           download
                                           class="px-3 py-1 border rounded text-xs hover:bg-green-50 transition">
                                            Descargar
                                        </a>

                                        {{-- BOTÓN SUBIR BOLETA FIRMADA --}}
                                        @if($tipoModelo === 'PlanillaDetalle' && $archivo->tipo === 'boleta' && $archivo->estado === 'generado')
                                            <button wire:click="abrirModalBoletaFirmada({{ $archivo->id }})"
                                                class="w-7 h-7 flex items-center justify-center
                                                    rounded-full border border-indigo-400
                                                    text-indigo-600 hover:bg-indigo-100
                                                    text-sm font-bold transition">
                                                +
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- MODAL SUBIR BOLETA FIRMA DIGITAL -->
        <x-jet-dialog-modal wire:model="mostrarModalBoletaFirmada" wire:loading.attr="disabled">
            <x-slot name="title" class="font-bold">
                <h1 class="font-bold text-xl">Recepción:</h1>
            </x-slot>

            <x-slot name="content">
                <div class="space-y-6">
                    {{-- Info empleado --}}
                    <div class="bg-gray-50 p-4 rounded-md border">
                        <h3 class="font-semibold text-sm text-gray-700">Empleado</h3>
                        @if($archivoSeleccionado && $archivoSeleccionado->archivoable)
                            <p class="text-sm mt-2">
                                {{ $archivoSeleccionado->archivoable->usuario->name ?? 'Empleado' }}
                            </p>
                            <p class="text-xs text-gray-500">
                                Documento: {{ $archivoSeleccionado->archivoable->usuario->dni ?? '-' }}
                            </p>
                        @endif
                    </div>

                    {{-- Checkbox de Aceptación --}}
                    <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-lg">
                        <label for="acepto" class="flex items-start cursor-pointer">
                            <div class="flex items-center h-5">
                                <x-jet-checkbox id="acepto" wire:model="acepto" class="h-5 w-5 text-indigo-600" />
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="font-medium text-indigo-900">
                                    "Recibí conforme, acepto y autorizo mi firma digital."
                                </span>
                                <p class="text-indigo-700 text-xs mt-1">
                                    Al marcar esta casilla, usted declara la conformidad de la boleta de pago recibida.
                                </p>
                            </div>
                        </label>
                        <x-jet-input-error for="acepto" class="mt-2" />
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-jet-secondary-button wire:click="$set('mostrarModalBoletaFirmada', false)" class="mx-2">
                    Cancelar
                </x-jet-secondary-button>

                <x-jet-button wire:click="guardarBoletaFirmada" 
                      wire:loading.attr="disabled" >
                    <span wire:loading.remove wire:target="guardarBoletaFirmada">Firmar Digitalmente</span>
                    <span wire:loading wire:target="guardarBoletaFirmada">Procesando...</span>
                </x-jet-button>
            </x-slot>
        </x-jet-dialog-modal>

    </div>
</div>


{{-- 
<div class="mt-16 flex justify-center">
    <div class="bg-white p-6 rounded shadow max-w-xl w-full">
        <h2 class="text-lg font-bold text-indigo-600 mb-4">Mis comprobantes</h2>

        @if($sinPlanilla)
            <div class="text-center py-8 text-gray-500">
                <i class="fa-regular fa-folder-open fa-2x mb-3"></i>
                <p class="text-sm">Aún no tienes comprobantes registrados.</p>
            </div>
        @else
            <div class="mb-4">
                <label class="text-sm font-medium">Periodo</label>
                <select wire:model="periodoSeleccionado" class="border rounded px-2 py-1 ml-2">
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo }}">{{ $periodo }}</option>
                    @endforeach
                </select>
            </div>

            @forelse($detalles as $detalle)
                @if($detalle->archivos->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <i class="fa-regular fa-folder-open fa-2x mb-3"></i>
                        <p class="text-sm text-gray-500">No tienes archivos en este periodo.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($detalle->archivos->groupBy('tipo') as $tipo => $archivosTipo)
                            <div>
                                <h4 class="text-xs font-medium uppercase text-gray-500 mb-2">{{ ucfirst($tipo) }}</h4>
                                <div class="space-y-2">
                                    @foreach($archivosTipo as $archivo)
                                        <div class="flex items-center justify-between border rounded p-2">
                                            <div class="flex items-center space-x-3">
                                                @if(in_array(strtolower($archivo->extension), ['jpg','jpeg','png']))
                                                    <img src="{{ Storage::url($archivo->ruta) }}" class="w-12 h-12 object-cover rounded" />
                                                @else
                                                    <div class="w-12 h-12 flex items-center justify-center bg-gray-100 rounded text-xs">PDF</div>
                                                @endif
                                                <div class="text-sm">
                                                    <div class="font-medium">{{ $archivo->nombre }}</div>
                                                    <div class="text-xs text-gray-500">
                                                        Subido: {{ $archivo->created_at->format('d/m/Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ Storage::url($archivo->ruta) }}" target="_blank" class="px-2 py-1 border rounded text-xs">Ver</a>
                                                <a href="{{ Storage::url($archivo->ruta) }}" download class="px-2 py-1 border rounded text-xs">Descargar</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @empty
                <p class="text-sm text-gray-500">No tienes archivos en este periodo.</p>
            @endforelse
        @endif
    </div>
</div>
--}}