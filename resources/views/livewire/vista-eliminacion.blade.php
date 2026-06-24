<div>
    @if ($eliminacion)
        <div class="block items-center mt-12">
            {{--<h1 class="mt-16 text-2xl text-center font-bold text-indigo-500 uppercase"> Eliminacion </h1>--}}
            <div class="flex justify-center mt-4">
                {{-- 
                <div class="bg-white rounded-xl p-8 w-4/6 shadow-lg">
                    <div class="flex flex-wrap items-center">
                        <p class="mr-4">Solicitado por: <strong class="px-2 bg-sky-200 rounded-xl">{{ $user->name ?? null}}</strong></p>
                        <p class="mr-4">Placa: 
                            <span class="px-2 bg-amber-200 rounded-xl">
                                {{ $certi ? (optional($certi->Vehiculo)->placa ?? 'NE') : ($eliminacion->placa ?? null) }}
                            </span>
                        </p>
                        <p class="mr-4">N° Formato: 
                            <span class="px-2 bg-amber-200 rounded-xl">
                                {{ $certi ? (optional($certi->Hoja)->numSerie ?? 'NE') : ($eliminacion->numSerieMaterial ?? null) }}
                            </span>
                        </p>
                        <p class="mr-4">N° Certificado: 
                            <span class="px-2 bg-amber-200 rounded-xl">
                                {{ $certi ? (optional($certi)->numSerie ?? 'NE') : ($eliminacion->numSerie ?? null) }}
                            </span>
                        </p>
                        <p class="mr-4">Servicio: 
                            <strong class="px-2 bg-sky-200 rounded-xl">
                                {{ $certi ? (optional($certi->Servicio->tipoServicio)->descripcion ?? 'NE') : ($eliminacion->tipoServicio ?? null) }}
                            </strong>
                        </p>

                        <p>Fecha: <span class="px-2 bg-amber-200 rounded-xl">{{ $eliminacion->created_at ?? null}}</span></p>
                    </div>

                    <div class="flex items-center justify-center mt-4" role="none">
                        @if ($certi && $certi->Hoja && $certi->Hoja->estado !== 3)
                            <button wire:click="$emit('deleteCertificacion', {{ $certi->id ?? null }})"
                                class="p-3 bg-indigo-500 rounded-xl text-white text-sm hover:font-bold hover:bg-indigo-700"
                                title="Eliminar servicio">
                                <i class="fa-solid fa-rectangle-xmark"></i>
                                Eliminar servicio
                            </button>
                        @else
                            <button class="p-3 bg-gray-400 rounded-xl text-white text-sm" style="cursor: not-allowed;"
                                disabled title="Ya se encuentra eliminado">
                                <i class="fa-solid fa-rectangle-xmark"></i>
                                Eliminar servicio
                            </button>
                        @endif
                    </div>
                </div>
                --}}
                <div class="bg-white rounded-xl p-8 w-4/6 shadow-lg border border-gray-100">
                    <div class="border-b pb-4 mb-6">
                        <h2 class="text-lg font-bold text-indigo-600">
                            Información de la Eliminación
                        </h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs uppercase text-gray-500">Solicitado por</p>
                            <span class="inline-block mt-1 px-3 py-1 bg-sky-100 text-sky-800 rounded-lg font-semibold">
                                {{ $user->name ?? null }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-gray-500">Placa</p>
                            <span class="inline-block mt-1 px-3 py-1 bg-amber-100 text-amber-800 rounded-lg font-semibold">
                                {{ $certi ? (optional($certi->Vehiculo)->placa ?? 'NE') : ($eliminacion->placa ?? null) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-gray-500">N° Formato</p>
                            <span class="inline-block mt-1 px-3 py-1 bg-orange-100 text-orange-800 rounded-lg font-semibold">
                                {{ $certi ? (optional($certi->Hoja)->numSerie ?? 'NE') : ($eliminacion->numSerieMaterial ?? null) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-gray-500">N° Certificado</p>
                            <span class="inline-block mt-1 px-3 py-1 bg-red-100 text-red-800 rounded-lg font-semibold">
                                {{ $certi ? (optional($certi)->numSerie ?? 'NE') : ($eliminacion->numSerie ?? null) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-gray-500">Servicio</p>
                            <span class="inline-block mt-1 px-3 py-1 bg-indigo-100 text-indigo-800 rounded-lg font-semibold">
                                {{ $certi ? (optional($certi->Servicio->tipoServicio)->descripcion ?? 'NE') : ($eliminacion->tipoServicio ?? null) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-gray-500">Fecha</p>
                            <span class="inline-block mt-1 px-3 py-1 bg-green-100 text-green-800 rounded-lg font-semibold">
                                {{ $eliminacion->created_at ?? null }}
                            </span>
                        </div>
                    </div>

                    <div class="border-t mt-6 pt-6 flex justify-center">
                        @if ($certi && $certi->Hoja && $certi->Hoja->estado !== 3)
                            <button wire:click="$emit('deleteCertificacion', {{ $certi->id ?? null }})"
                                class="p-3 bg-indigo-500 rounded-xl text-white text-sm hover:font-bold hover:bg-indigo-700"
                                title="Eliminar servicio">
                                <i class="fa-solid fa-trash-can mr-2"></i>
                                Eliminar servicio
                            </button>
                        @else
                            <button class="p-3 bg-gray-400 rounded-xl text-white text-sm" style="cursor: not-allowed;"
                                disabled title="Ya se encuentra eliminado">
                                <i class="fa-solid fa-circle-check mr-2"></i>
                                Eliminar servicio
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        Livewire.on('deleteCertificacion', certificacionId => {
            Swal.fire({
                title: '¿Estas seguro de eliminar este servicio?',
                text: "una vez eliminado este registro, no podras recuperarlo.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.emitTo('vista-eliminacion', 'delete', certificacionId);
                    /*Swal.fire(
                        'Listo!',
                        'Servicio eliminado correctamente.',
                        'success'
                    )*/
                }
            })
        });
    </script>

</div>
