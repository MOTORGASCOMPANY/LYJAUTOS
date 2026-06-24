<div>
    @if ($anulacion)
        <div class="block items-center mt-12">
            <div class="flex justify-center mt-4">
                <div class="bg-white rounded-xl p-8 w-3/6 shadow-lg border border-gray-100">

                    <div class="border-b pb-4 mb-6">
                        <h2 class="text-lg font-bold text-indigo-600">
                            Información de la Anulación
                        </h2>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- COLUMNA IZQUIERDA -->
                        <div>
                            <h3 class="text-sm font-bold text-gray-600 uppercase mb-4">
                                Datos de la Solicitud
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                                <div>
                                    <p class="text-xs uppercase text-gray-500">Solicitado por</p>
                                    <span
                                        class="inline-block mt-1 px-3 py-1 bg-sky-100 text-sky-800 rounded-lg font-semibold">
                                        {{ $user->name ?? null }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs uppercase text-gray-500">Placa</p>
                                    <span
                                        class="inline-block mt-1 px-3 py-1 bg-amber-100 text-amber-800 rounded-lg font-semibold">
                                        {{ $certi->Vehiculo->placa ?? 'NE' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs uppercase text-gray-500">N° Formato</p>
                                    <span
                                        class="inline-block mt-1 px-3 py-1 bg-orange-100 text-orange-800 rounded-lg font-semibold">
                                        {{-- $certi->Hoja->numSerie ?? 'NE' --}}
                                        {{ $anulacion->materialSustituir->numSerie ?? 'NE' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs uppercase text-gray-500">Nuevo N° Formato</p>
                                    <span class="inline-block mt-1 px-3 py-1 bg-orange-100 text-orange-800 rounded-lg font-semibold">
                                        {{ $anulacion->nuevoMaterial->numSerie ?? 'NE' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs uppercase text-gray-500">N° Certificado</p>
                                    <span
                                        class="inline-block mt-1 px-3 py-1 bg-red-100 text-red-800 rounded-lg font-semibold">
                                        {{ $certi->numSerie ?? 'NE' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs uppercase text-gray-500">Servicio</p>
                                    <span
                                        class="inline-block mt-1 px-3 py-1 bg-indigo-100 text-indigo-800 rounded-lg font-semibold">
                                        {{ $certi->Servicio->tipoServicio->descripcion ?? 'NE' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs uppercase text-gray-500">Motivo</p>
                                    <span
                                        class="inline-block mt-1 px-3 py-1 bg-green-100 text-green-800 rounded-lg font-semibold">
                                        {{ $anulacion->motivo }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!-- COLUMNA DERECHA -->
                        @if (isset($images))
                            <div>
                                <h3 class="text-sm font-bold text-gray-600 uppercase mb-4">
                                    Evidencia Adjunta
                                </h3>
                                <div class="border rounded-xl p-3 bg-gray-50">
                                    <div class="w-full items-center justify-center ">
                                        <img alt="gallery" class="mx-auto flex object-cover object-center w-full rounded-lg"
                                            src="{{ Storage::url($images->ruta) }}" style="max-width: 500px; max-height: 800px;">
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center justify-center">
                                    <a class="group max-w-max relative mx-1 flex flex-col items-center justify-center rounded-full bg-white border border-gray-500 p-1 text-gray-500 hover:bg-gray-200 hover:text-gray-600"
                                        href="#">
                                        <p class="flex m-auto">
                                            <i class="fas fa-info-circle"></i>
                                        </p>
                                        <div class="[transform:perspective(50px)_translateZ(0)_rotateX(10deg)] group-hover:[transform:perspective(0px)_translateZ(0)_rotateX(0deg)] absolute bottom-0 mb-6 origin-bottom transform rounded text-white opacity-0 transition-all duration-300 group-hover:opacity-100 z-10">
                                            <div class="flex w-56 flex-col items-center">
                                                <div class="rounded bg-gray-900 p-2 text-xs text-center shadow-lg">
                                                    Información:
                                                    <p class="text-xs">Cargado el: {{ $images->created_at }}</p>
                                                </div>
                                                <div class="clip-bottom h-2 w-4 bg-gray-900 text-xs"></div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="border-t mt-6 pt-6 flex justify-center">
                        {{--@if ($certi && $certi->Hoja && $certi->Hoja->estado !== 5)--}}
                        @if ($anulacion && $anulacion->materialSustituir && $anulacion->materialSustituir->estado !== 5)
                            <button wire:click="$emit('anularMaterial',{{ $certi->id }})"
                                class="p-3 bg-indigo-500 rounded-xl text-white text-sm hover:font-bold hover:bg-indigo-700"
                                title="Anular servicio">
                                <i class="fa-solid fa-trash-can mr-2"></i>
                                Anular formato y sustituir
                            </button>
                            {{-- 
                            @if ($certi->Servicio->tipoServicio->id == 10)
                                <button wire:click="$emit('anularCertificacionChip',{{ $certi->id }})"
                                    class="p-3 bg-indigo-500 rounded-xl text-white text-sm hover:font-bold hover:bg-indigo-700"
                                    title="Anular servicio + Chip">
                                    <i class="fa-solid fa-trash-can mr-2"></i>
                                    AnularServicio + Chip
                                </button>
                            @else
                                <button wire:click="$emit('anularCertificacion',{{ $certi->id }})"
                                    class="p-3 bg-indigo-500 rounded-xl text-white text-sm hover:font-bold hover:bg-indigo-700"
                                    title="Anular servicio">
                                    <i class="fa-solid fa-trash-can mr-2"></i>
                                    Anular servicio
                                </button>
                            @endif
                            --}}
                        @else
                            <button class="p-3 bg-gray-400 rounded-xl text-white text-sm" style="cursor: not-allowed;"
                                disabled title="Ya se encuentra anulado">
                                <i class="fa-solid fa-circle-check mr-2"></i>
                                Anular formato y sustituir
                            </button>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    @endif

    {{-- JS --}}
    @push('js')
        <script>
            Livewire.on('anularMaterial', certificacionId => {
                Swal.fire({
                    title: '¿Seguro que quieres anular el formato?',
                    text: "Al anular el formato asociado quedará inutilizable",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Si, anular'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.emitTo('vista-solicitud-anul', 'aprobarSustitucion', certificacionId);
                        /*Swal.fire(
                            'Listo!',
                            'Servicio anulado correctamente.',
                            'success'
                        )*/
                    }
                })
            });
        </script>
        {{-- 
        <script>
            Livewire.on('anularCertificacionChip', certificacionId => {
                Swal.fire({
                    title: '¿Seguro que quieres anular este servicio?',
                    text: "Al anular este servicio el formato asociado quedará inutilizable",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Si, anular'
                }).then((result) => {
                    if (result.isConfirmed) {

                        Livewire.emitTo('vista-solicitud-anul', 'anularchip', certificacionId);

                        Swal.fire(
                            'Listo!',
                            'Servicio anulado correctamente.',
                            'success'
                        )
                    }
                })
            });
        </script>
        --}}
    @endpush

</div>
