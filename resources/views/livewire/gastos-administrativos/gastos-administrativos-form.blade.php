<div class="mt-16 flex justify-center">
    <div class="bg-white p-6 rounded-xl shadow max-w-5xl w-full space-y-4">
        <!-- TÍTULO -->
        <div>
            <h2 class="text-xl font-bold text-indigo-600 flex items-center gap-2">
                <i class="fa-solid fa-file-invoice-dollar"></i>
                Gastos Administrativos
            </h2>
            <p class="text-sm text-gray-500">
                Periodo {{ str_pad($gasto->periodo_mes, 2, '0', STR_PAD_LEFT) }}/{{ $gasto->periodo_anio }}
            </p>
        </div>
        <!-- TOTAL GENERAL -->
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 flex justify-between items-center">
            <span class="text-sm font-medium text-indigo-700">
                Total mensual
            </span>
            <span class="text-2xl font-bold text-indigo-800">
                S/ {{ number_format($gasto->total, 2) }}
            </span>
        </div>

        <div class="flex flex-col gap-8">
            <!-- GASTOS POR SERVICIOS -->
            <div class="space-y-4" x-data="{ openForm: false }">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold uppercase text-gray-600 flex items-center gap-2">
                        <i class="fa-solid fa-briefcase text-indigo-500"></i>
                        Gastos por servicios
                    </h3>
                    <!-- Botón para mostrar/ocultar -->
                    <button @click="openForm = !openForm" type="button"
                        class="text-xs font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition-all">
                        <i class="fa-solid" :class="openForm ? 'fa-minus-circle' : 'fa-plus-circle'"></i>
                        <span x-text="openForm ? 'Cancelar' : 'Agregar nuevo servicio'"></span>
                    </button>
                </div>
                <!-- FORMULARIO SERVICIOS -->
                <div x-show="openForm" x-collapse x-cloak>
                    <form wire:submit.prevent="agregarServicio"
                        class="grid grid-cols-1 md:grid-cols-12 gap-3 bg-gray-50 p-4 rounded-lg border items-end">
                        <!-- CONCEPTO -->
                        <div class="md:col-span-3">
                            <x-jet-label value="Concepto" class="text-xs" />
                            <input type="text" list="conceptos-servicios" wire:model.defer="concepto"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-200 focus:border-indigo-300 text-sm"
                                placeholder="Ej: Alquiler local" />
                            <datalist id="conceptos-servicios">
                                <option value="Alquiler local">
                                <option value="Internet">
                                <option value="Motorizados">
                                <option value="Luz">
                                <option value="Agua">
                                <option value="Celulares">
                                <option value="Materiales de Oficina">
                                <option value="Sistema">
                                <option value="Planilla">
                                <option value="AFP">
                                <option value="ONP">
                                <option value="Impuestos a la Renta">
                                <option value="IGV">
                            </datalist>
                            @error('concepto')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <!-- PRESUPUESTADO -->
                        <div class="md:col-span-2">
                            <x-jet-label value="Presupuestado (S/)" class="text-xs" />
                            <x-jet-input type="number" step="0.01" wire:model.defer="monto_presupuestado"
                                class="mt-1 block w-full text-sm" placeholder="0.00" />
                            @error('monto_presupuestado')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <!-- MONTO -->
                        <div class="md:col-span-2">
                            <x-jet-label value="Monto (S/)" class="text-xs" />
                            <x-jet-input type="number" step="0.01" wire:model.defer="monto"
                                class="mt-1 block w-full text-sm" placeholder="0.00" />
                            @error('monto')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <!-- PROVEEDOR -->
                        <div class="md:col-span-3">
                            <x-jet-label value="Proveedor (opcional)" class="text-xs" />
                            <x-jet-input type="text" wire:model.defer="proveedor" class="mt-1 block w-full text-sm"
                                placeholder="Ej: Telefónica SAC" />
                        </div>
                        <!-- BOTÓN ALINEADO -->
                        <div class="md:col-span-2">
                            <x-jet-button>
                                <i class="fa-solid fa-plus mr-1"></i>
                                servicio
                            </x-jet-button>
                        </div>
                    </form>
                </div>
                <!-- LISTADO SERVICIOS -->
                @if ($gasto->servicios->count())
                    <div class="overflow-x-auto border rounded-lg shadow-sm">
                        <table class="min-w-full text-sm divide-y divide-gray-200">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-3 py-2 text-left">Concepto</th>
                                    <th class="px-3 py-2 text-center">Presupuestado</th>
                                    <th class="px-3 py-2 text-center">Monto Real</th>
                                    <th class="px-3 py-2 text-center">Pagado</th>
                                    <th class="px-3 py-2 text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($gasto->servicios as $servicio)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2">
                                            <div class="flex items-center justify-between gap-2">
                                                {{-- <div class="flex items-center gap-2"> --}}
                                                <span class="truncate">
                                                    {{ $servicio->concepto }}
                                                </span>
                                                <button wire:click="abrirModalSubservicio({{ $servicio->id }})"
                                                    type="button"
                                                    class="w-6 h-6 flex items-center justify-center rounded-full
                                                        bg-indigo-100 text-indigo-600 text-xs font-bold
                                                        hover:bg-indigo-200 transition">
                                                    +
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-700">
                                            S/ {{ number_format($servicio->monto_presupuestado, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold text-indigo-700">
                                            S/ {{ number_format($servicio->monto, 2) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-center">
                                                <input type="checkbox" wire:click="togglePago({{ $servicio->id }})"
                                                    @checked($servicio->pagado)
                                                    class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-center items-center space-x-2">
                                                <button type="button" wire:click="$emit('abrirGastoServicios', {{ $servicio->id }})"
                                                    class="group flex py-2 px-2 text-center items-center rounded-md bg-yellow-300 font-bold text-white cursor-pointer hover:bg-yellow-400 hover:animate-pulse">
                                                    <i class="fa-solid fa-folder"></i>
                                                    <span class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute left-1/2-translate-x-1/2 translate-y-full opacity-0 m-4 mx-auto z-50">
                                                        Archivos
                                                    </span>
                                                </button>
                                                <button wire:click="editarServicio({{ $servicio->id }})"
                                                    class="group flex py-2 px-2 text-center items-center rounded-md bg-blue-300 font-bold text-white cursor-pointer hover:bg-blue-400 hover:animate-pulse">
                                                    <i class="fa fa-pencil"></i>
                                                    <span class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute left-1/2-translate-x-1/2 translate-y-full opacity-0 m-4 mx-auto z-50">
                                                        Editar
                                                    </span>
                                                </button>
                                                <button type="button" wire:click="$emit('deleteServicio',{{ $servicio->id }})"
                                                    class="group flex py-2 px-2 text-center items-center rounded-md bg-red-400 font-bold text-white cursor-pointer hover:bg-red-500 hover:animate-pulse">
                                                    <i class="fa fa-trash"></i>
                                                    <span class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute left-1/2-translate-x-1/2 translate-y-full opacity-0 m-4 mx-auto z-50">
                                                        Eliminar
                                                    </span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- SUBSERVICIOS -->
                                    @if ($servicio->subservicios->count())
                                        @foreach ($servicio->subservicios as $sub)
                                            <tr class="bg-gray-50 text-xs border-t">
                                                <td colspan="2" class="px-6 py-2 text-gray-600">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-indigo-400">↳</span>
                                                        <span class="font-medium text-gray-700">
                                                            {{ $sub->fecha ? \Carbon\Carbon::parse($sub->fecha)->format('d/m/Y') : '—' }}
                                                        </span>
                                                        @if (!empty($sub->descripcion))
                                                            <span class="text-gray-400">– {{ $sub->descripcion }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2 text-center text-indigo-600 font-medium">
                                                    S/ {{ number_format($sub->monto, 2) }}
                                                </td>
                                                <td class="px-4 py-2 text-center">
                                                    @if ($sub->pagado)
                                                        <span class="text-green-600 font-semibold">Sí</span>
                                                    @else
                                                        <span class="text-gray-400">No</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2">
                                                    <div class="flex justify-center">
                                                        <button type="button" wire:click="$emit('deleteSubservicio', {{ $sub->id }})"
                                                            class="group flex py-2 px-2 text-center items-center rounded-md bg-red-400 font-bold text-white cursor-pointer hover:bg-red-500 hover:animate-pulse">
                                                            <i class="fa fa-trash"></i>
                                                            <span class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute left-1/2-translate-x-1/2 translate-y-full opacity-0 m-4 mx-auto z-50">
                                                                Eliminar
                                                            </span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-100 border-t-2 border-gray-300">
                                <tr>
                                    <td class="px-3 py-3 text-right text-gray-600">TOTALES:</td>
                                    <td class="px-4 py-3 text-center text-gray-700">
                                        S/ {{ number_format($gasto->servicios->sum('monto_presupuestado'), 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-semibold text-indigo-700">
                                        S/ {{ number_format($gasto->servicios->sum('monto'), 2) }}
                                    </td>
                                    <td colspan="2"></td> </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center text-sm text-gray-400 py-6">
                        <i class="fa-regular fa-folder-open text-2xl mb-2"></i>
                        <p>No hay servicios registrados.</p>
                    </div>
                @endif

                <!-- Componente archivos -->
                @livewire('gastos-administrativos.gastos-servicios-archivos')
            </div>

            <!-- GASTOS POR PERSONAL -->
            <div class="space-y-4" x-data="{ openFormPersonal: false }">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold uppercase text-gray-600 flex items-center gap-2">
                        <i class="fa-solid fa-users text-indigo-500"></i>
                        Gastos por personal
                    </h3>
                    <!-- Botón Toggle -->
                    <button @click="openFormPersonal = !openFormPersonal" type="button"
                        class="text-xs font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition-all">
                        <i class="fa-solid" :class="openFormPersonal ? 'fa-minus-circle' : 'fa-plus-circle'"></i>
                        <span x-text="openFormPersonal ? 'Cancelar' : 'Agregar personal'"></span>
                    </button>
                </div>
                <div class="bg-blue-50 border-l-4 border-blue-400 p-3 rounded-r-lg shadow-sm">
                    <div class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-info text-blue-500 mt-1"></i>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full">
                            <!-- Columna 1: Gratificación -->
                            <div>
                                <h4 class="text-[11px] font-bold uppercase text-blue-700 mb-1">Cálculo de Gratificación
                                </h4>
                                <ul class="text-[10px] text-blue-600 leading-tight space-y-1">
                                    <li>• <strong>Monto:</strong> ((50% Sueldo / 6) * Meses) + Asignación</li>
                                    <li>• <strong>Bonif. 9%:</strong> Monto * 0.09</li>
                                    <li>• <strong>Total Grati:</strong> Monto + Bonificación</li>
                                </ul>
                            </div>
                            <!-- Columna 2: CTS  -->
                            <div>
                                <h4 class="text-[11px] font-bold uppercase text-blue-700 mb-1">Cálculo CTS / Otros</h4>
                                <ul class="text-[10px] text-blue-600 leading-tight space-y-1">
                                    <li>• <strong>SueldAsig:</strong> Sueldo + Asignacion</li>
                                    <li>• <strong>QuincenaGrati:</strong> (50% SueldAsig + 1/6 del 50% SueldAsig)</li>
                                    <li>• <strong>Total CTS:</strong> (QuincenaGrati * Meses) / 12</li>
                                </ul>
                            </div>
                            <!-- Columna 3: OTROS  -->
                            <div>
                                <h4 class="text-[11px] font-bold uppercase text-blue-700 mb-1"> Otros</h4>
                                <ul class="text-[10px] text-blue-600 leading-tight space-y-1">
                                    <li>• <strong>EsSalud:</strong> 9% del sueldo bruto</li>
                                    <li>• <strong>Planilla:</strong> </li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- FORMULARIO PERSONAL -->
                <div x-show="openFormPersonal" x-collapse x-cloak>
                    <div class="space-y-3">
                        <form wire:submit.prevent="agregarPersonal" class="grid grid-cols-1 md:grid-cols-4 gap-2 bg-gray-50 p-4 rounded-lg border items-end">
                            <div class="md:col-span-2">
                                <x-jet-label value="Empleado" class="text-xs" />
                                <select wire:model="empleado_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    <option value="">-- Seleccione empleado --</option>
                                    @foreach (\App\Models\User::orderBy('name')->get() as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                                <x-jet-input-error for="empleado_id" />
                            </div>
                            <div>
                                <x-jet-label value="Sueldo" class="text-xs" />
                                <x-jet-input type="number" readonly class="mt-1 block w-full text-sm bg-gray-100"
                                    wire:model="sueldo" />
                                <x-jet-input-error for="sueldo" />
                            </div>
                            <div>
                                <x-jet-label value="Gratificación" class="text-xs" />
                                <x-jet-input type="number" readonly wire:model="gratificacion"
                                    class="mt-1 block w-full text-sm bg-gray-100" />
                                <x-jet-input-error for="gratificacion" />
                            </div>
                            <div>
                                <x-jet-label value="CTS" class="text-xs" />
                                <x-jet-input type="number" readonly class="mt-1 block w-full text-sm bg-gray-100"
                                    wire:model="cts" />
                                <x-jet-input-error for="cts" />
                            </div>
                            <div>
                                <x-jet-label value="EsSalud" class="text-xs" />
                                <x-jet-input type="number" readonly wire:model="essalud"
                                    class="mt-1 block w-full text-sm bg-gray-100" />
                                <x-jet-input-error for="essalud" />
                            </div>
                            <div>
                                <x-jet-label value="Planilla" class="text-xs" />
                                <x-jet-input type="number" step="0.01" wire:model="planilla"
                                    class="mt-1 block w-full text-sm" />
                                <x-jet-input-error for="planilla" />
                            </div>
                            <div>
                                <x-jet-label value="Otros" class="text-xs" />
                                <x-jet-input type="number" step="0.01" wire:model="otros"
                                    class="block w-full text-sm" />
                                <x-jet-input-error for="otros" />
                            </div>
                            <div class="md:col-span-4 flex justify-end mt-2">
                                <x-jet-button>
                                    <i class="fa-solid fa-plus mr-1"></i>
                                    Agregar personal
                                </x-jet-button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- LISTADO PERSONAL -->
                @if ($gasto->personal->count())
                    <div class="overflow-x-auto border rounded-lg shadow-sm">
                        <table class="min-w-full text-sm divide-y divide-gray-200">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-3 py-2 text-left">Empleado</th>
                                    <th class="px-3 py-2 text-center">Sueldo</th>
                                    <th class="px-3 py-2 text-center">Gratificación</th>
                                    <th class="px-3 py-2 text-center">CTS</th>
                                    <th class="px-3 py-2 text-center">EsSalud</th>
                                    <th class="px-3 py-2 text-center">Planilla</th>
                                    <th class="px-3 py-2 text-center">Vacación</th>
                                    <th class="px-3 py-2 text-center">Total</th>
                                    <th class="px-3 py-2 text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($gasto->personal as $p)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-3 py-2 text-gray-800">
                                            {{ $p->user->name }}
                                        </td>
                                        <td class="px-3 py-2 text-center text-gray-600">
                                            {{ number_format($p->sueldo, 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-center text-gray-600">
                                            {{ number_format($p->gratificacion, 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-center text-gray-600">
                                            {{ number_format($p->cts, 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-center text-gray-600">
                                            {{ number_format($p->essalud, 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-center text-gray-600">
                                            {{ number_format($p->planilla, 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-center text-gray-600">
                                            {{ number_format($p->vacacion, 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-center font-semibold text-indigo-700">
                                            S/ {{ number_format($p->total, 2) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-center items-center space-x-2">
                                                <button wire:click="editarPersonal({{ $p->id }})"
                                                    class="group flex py-2 px-2 text-center items-center rounded-md bg-blue-300 font-bold text-white cursor-pointer hover:bg-blue-400 hover:animate-pulse">
                                                    <i class="fa fa-pencil"></i>
                                                    <span class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute left-1/2-translate-x-1/2 translate-y-full opacity-0 m-4 mx-auto z-50">
                                                        Editar
                                                    </span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-100 border-t-2 border-gray-300">
                                <tr>
                                    <td colspan="7" class="px-3 py-3 text-right text-gray-600">TOTALES:</td>
                                    <td class="px-4 py-3 text-center font-semibold text-indigo-700">
                                        S/ {{ number_format($gasto->personal->sum('total'), 2) }}
                                    </td>
                                    <td></td>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center text-sm text-gray-400 py-6">
                        <i class="fa-regular fa-folder-open text-2xl mb-2"></i>
                        <p>No hay personal registrado.</p>
                    </div>
                @endif
            </div>            
            
        </div>

        <!-- MODAL EDITAR GASTOS POR PERSONAL -->
        <x-jet-dialog-modal wire:model="mostrarModalPersonal">
            <x-slot name="title">
                Editar personal
            </x-slot>
            <x-slot name="content">
                <div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <x-jet-label value="Sueldo" />
                            <x-jet-input type="number" step="0.01" class="w-full" wire:model.defer="edit_sueldo" />
                            <x-jet-input-error for="edit_sueldo" class="mt-2" />
                        </div>
                        <div>
                            <x-jet-label value="Gratificación" />
                            <x-jet-input type="number" step="0.01" class="w-full" wire:model.defer="edit_gratificacion" />
                            <x-jet-input-error for="edit_gratificacion" class="mt-2" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <x-jet-label value="CTS" />
                            <x-jet-input type="number" step="0.01" class="w-full" wire:model.defer="edit_cts" />
                            <x-jet-input-error for="edit_cts" class="mt-2" />
                        </div>
                        <div>
                            <x-jet-label value="EsSalud" />
                            <x-jet-input type="number" step="0.01" class="w-full" wire:model.defer="edit_essalud" />
                            <x-jet-input-error for="edit_essalud" class="mt-2" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <x-jet-label value="Planilla" />
                            <x-jet-input type="number" step="0.01" wire:model.defer="edit_planilla" class="w-full" />
                            <x-jet-input-error for="edit_planilla" class="mt-2" />
                        </div>
                        <div>
                            <x-jet-label value="Vacacion" />
                            <x-jet-input type="number" step="0.01" wire:model.defer="edit_vacacion" class="w-full" />
                            <x-jet-input-error for="edit_vacacion" class="mt-2" />
                        </div>
                    </div>
                </div>
            </x-slot>
            <x-slot name="footer">
                <x-jet-secondary-button wire:click="$set('mostrarModalPersonal',false)" class="mx-2">
                    Cancelar
                </x-jet-secondary-button>
                <x-jet-button wire:click="actualizarPersonal" wire:loading.attr="disabled"
                    wire:target="actualizarPersonal">
                    Guardar
                </x-jet-button>
            </x-slot>
        </x-jet-dialog-modal>

        <!-- MODAL EDITAR GASTOS POR SERVICIO -->
        <x-jet-dialog-modal wire:model="mostrarModalServicio">
            <x-slot name="title">
                Editar servicio
            </x-slot>
            <x-slot name="content">
                <div class="space-y-4">
                    <div>
                        <x-jet-label value="Concepto" />
                        <x-jet-input wire:model.defer="edit_concepto" class="w-full" />
                        <x-jet-input-error for="edit_concepto" class="mt-2" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-jet-label value="Monto presupuestado" />
                            <x-jet-input type="number" step="0.01" wire:model.defer="edit_monto_presupuestado"
                                class="w-full" />
                            <x-jet-input-error for="edit_monto_presupuestado" class="mt-2" />
                        </div>

                        <div>
                            <x-jet-label value="Monto real" />
                            <x-jet-input type="number" step="0.01" wire:model.defer="edit_monto"
                                class="w-full" />
                            <x-jet-input-error for="edit_monto" class="mt-2" />
                        </div>
                    </div>
                    <div>
                        <x-jet-label value="Proveedor" />
                        <x-jet-input wire:model.defer="edit_proveedor" class="w-full" />
                        <x-jet-input-error for="edit_proveedor" class="mt-2" />
                    </div>
                </div>
            </x-slot>
            <x-slot name="footer">
                <x-jet-secondary-button wire:click="$set('mostrarModalServicio',false)" class="mx-2">
                    Cancelar
                </x-jet-secondary-button>
                <x-jet-button wire:click="actualizarServicio" wire:loading.attr="disabled"
                    wire:target="actualizarServicio">
                    Guardar
                </x-jet-button>
            </x-slot>
        </x-jet-dialog-modal>

        <!-- MODAL AGREGAR SUBSERVICIO -->
        <x-jet-dialog-modal wire:model="mostrarModalSubservicio">
            <x-slot name="title">
                Agregar detalle subservicio
            </x-slot>
            <x-slot name="content">
                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-jet-label value="Monto (S/)" />
                            <x-jet-input type="number" step="0.01" wire:model.defer="sub_monto"
                                class="w-full" />
                            <x-jet-input-error for="sub_monto" class="mt-1" />
                        </div>
                        <div>
                            <x-jet-label value="Fecha de pago" />
                            <x-date-picker wire:model="sub_fecha" placeholder="Seleccionar fecha"
                                class="bg-gray-50 border border-gray-300 rounded-md block w-full" />
                            <x-jet-input-error for="sub_fecha" class="mt-1" />
                        </div>
                    </div>
                    <div>
                        <x-jet-label value="Descripcion (opcional)" />
                        <x-jet-input type="text" wire:model.defer="sub_descripcion" class="w-full"
                            placeholder="Ej: Luz del Sur SAC" />
                    </div>
                </div>
            </x-slot>
            <x-slot name="footer">
                <x-jet-secondary-button wire:click="$set('mostrarModalSubservicio', false)" class="mx-2">
                    Cancelar
                </x-jet-secondary-button>
                <x-jet-button wire:click="guardarSubservicio" wire:loading.attr="disabled"
                    wire:target="guardarSubservicio">
                    Guardar
                </x-jet-button>
            </x-slot>
        </x-jet-dialog-modal>

    </div>

    {{-- JS --}}
    @push('js')
        <script>
            Livewire.on('deleteServicio', servicioId => {
                Swal.fire({
                    title: '¿Estas seguro de eliminar este servicio?',
                    text: "una vez eliminado este registro, no podras recuperarlo.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.emitTo('gastos-administrativos.gastos-administrativos-form', 'eliminarServicio', servicioId);
                    }
                })
            });

            // NUEVO: Listener para la confirmación de subservicios
            Livewire.on('deleteSubservicio', subservicioId => {
                Swal.fire({
                    title: '¿Estás seguro de eliminar este subservicio?',
                    text: "El monto se restará automáticamente del servicio principal.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.emitTo('gastos-administrativos.gastos-administrativos-form', 'eliminarSubservicio', subservicioId);
                    }
                })
            });
        </script>
    @endpush
</div>
