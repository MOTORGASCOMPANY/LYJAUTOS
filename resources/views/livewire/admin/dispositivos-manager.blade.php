<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-gray-200 p-6 rounded-lg shadow-lg mb-6">

            <h1 class="text-2xl font-bold text-indigo-600 tracking-tight">Autorizar esta Estación de Trabajo</h1>
            <span class="text-xs">Autorizar este dispotivo para su uso</span>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-jet-label value="Nombre de la Estación " />
                    <x-jet-input type="text" placeholder="Ej: PC-RECEPCION-01" wire:model.defer="nombre_estacion" class="w-full" />
                </div>
                <div>
                    <x-jet-label value="Ubicación / Taller" />
                    <x-jet-input type="text" wire:model.defer="descripcion_ubicacion" class="w-full" />
                </div>
            </div>
            <button wire:click="autorizarEstaEstacion" class="mt-4 bg-indigo-500 text-white px-6 py-2 rounded-lg font-bold">
                REGISTRAR Y AUTORIZAR ESTA LAPTOP
            </button>
        </div>

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3">Estación</th>
                        <th class="p-3">SO/Navegador</th>
                        <th class="p-3">Última IP</th>
                        <th class="p-3">Estado</th>
                        <th class="p-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dispositivos as $dis)
                    <tr class="border-t">
                        <td class="p-3">
                            <span class="font-bold">{{ $dis->nombre_estacion }}</span><br>
                            <span class="text-xs text-gray-500">{{ $dis->descripcion_ubicacion }}</span>
                        </td>
                        <td class="p-3 text-sm">{{ $dis->sistema_operativo }} / {{ $dis->navegador }}</td>
                        <td class="p-3 text-sm">{{ $dis->ultima_ip }}</td>
                        <td class="p-3">
                            @if($dis->esta_activo)
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Activo</span>
                            @else
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Inactivo</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <button wire:click="desactivar({{ $dis->id }})" class="text-red-600 text-xs font-bold">Revocar</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        window.addEventListener('save-device-token', event => {
            localStorage.setItem('mtg_device_token', event.detail.token);
            alert('¡Sello digital instalado en este navegador con éxito!');
        });
    </script>
</div>