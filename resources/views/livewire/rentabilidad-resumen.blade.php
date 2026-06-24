<div>
    <div class="sm:px-6 w-full pt-12 pb-4">
        <div class="bg-gray-200  px-8 py-4 rounded-xl w-full ">
            <div class=" items-center md:block sm:block">
                <div class="p-2 w-64 my-4 md:w-full">
                    <h2 class="text-indigo-600 font-bold text-3xl">
                        <i class="fa-solid fa-square-poll-vertical fa-xl"></i>
                        &nbsp;RENTABILIDAD RESUMEN
                    </h2>
                </div>
                <div class="flex flex-wrap items-center space-x-2">
                    <div class="flex items-center space-x-2">
                        <div class="flex bg-white items-center p-2 w-1/2 md:w-64 rounded-md mb-4">
                            <span>Desde:</span>
                            <input type="date" wire:model="fechaInicio" placeholder="Fecha de inicio"
                                class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                        </div>
                        <div class="flex bg-white items-center p-2 w-1/2 md:w-64 rounded-md mb-4">
                            <span>Hasta:</span>
                            <input type="date" wire:model="fechaFin" placeholder="Fecha de Fin"
                                class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                        </div>
                    </div>
                    <button wire:click="reportes()"
                        class="bg-green-400 px-6 py-4 w-full md:w-auto rounded-md text-white font-semibold tracking-wide cursor-pointer mb-4">
                        <p class="truncate"> Generar reporte </p>
                    </button>
                </div>
                <div class="w-auto my-4">
                    <x-jet-input-error for="fechaInicio" />
                    <x-jet-input-error for="fechaFin" />
                </div>
                <div class="w-full text-center font-semibold text-gray-100 p-4 mb-4 border rounded-md bg-indigo-400 shadow-lg"
                    wire:loading>
                    CARGANDO <i class="fa-solid fa-spinner animate-spin"></i>
                </div>
            </div>
        </div>

        <!-- TABLA REPORTE EXTERNOS -->
        @if ($reporteExternos)
            <div class="bg-gray-200  px-8 py-4 rounded-xl w-full mt-4">
                <div class="overflow-x-auto m-auto w-full">
                    <div class="inline-block min-w-full py-2 sm:px-6">
                        <div class="overflow-hidden">
                            <table class="min-w-full border text-center text-sm font-light rounded-xl overflow-hidden">
                                <thead class="font-medium dark:border-neutral-500">
                                    <tr>
                                        <th scope="col" class="text-center text-indigo-600 text-xl font-bold mb-4"
                                            colspan="7">
                                            {{ 'Reporte Externos ' . $fechaInicio . ' al ' . $fechaFin }}
                                        </th>
                                    </tr>
                                    <tr>
                                        <td colspan="7" style="height: 20px;"></td>
                                    </tr>
                                    <tr class="bg-indigo-200">
                                        <th scope="col" class="border-r px-6 py-4">
                                            #
                                        </th>
                                        <th scope="col" class="border-r px-6 py-4">
                                            Inspector
                                        </th>
                                        <th scope="col" class="border-r px-6 py-4">
                                            Ingresos Totales
                                        </th>
                                        <th scope="col" class="border-r px-6 py-4">
                                            % Ventas
                                        </th>
                                        <th scope="col" class="border-r px-6 py-4">
                                            Gastos Administrativos
                                        </th>
                                        <th scope="col" class="border-r px-6 py-4">
                                            Costos Totales
                                        </th>
                                        <th scope="col" class="border-r px-6 py-4">
                                            Rentabilidad
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reporteExternos as $data)
                                        <tr class="bg-orange-200 hover:bg-orange-300">
                                            <td class="whitespace-nowrap border-r px-6 py-3">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="whitespace-nowrap border-r px-6 py-3">
                                                {{ $data['inspector'] }}
                                            </td>
                                            <td class="whitespace-nowrap border-r px-6 py-3">
                                                {{ number_format($data['total'], 2) }}
                                            </td>
                                            <td class="whitespace-nowrap border-r px-6 py-3">
                                                {{ number_format($data['porcentaje'], 2) }} %
                                            </td>
                                            <td class="whitespace-nowrap border-r px-6 py-3">
                                                {{ number_format($data['gastos_adm'], 2) }}
                                            </td>
                                            <td class="whitespace-nowrap border-r px-6 py-3">
                                                {{ number_format($data['costos_totales'], 2) }}
                                            </td>
                                            <td class="whitespace-nowrap border-r px-6 py-3">
                                                {{ number_format($data['rentabilidad'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-green-200">
                                        <td colspan="2" class="border-r px-6 py-3 font-bold text-right">
                                            CIERRE DE EXTERNOS:
                                        </td>
                                        <td class="border-r px-6 py-3 font-bold">
                                            {{ number_format(collect($reporteExternos)->sum('total'), 2) }}
                                        </td>
                                        <td class="border-r px-6 py-3 font-bold">
                                            {{ number_format($totalExternos > 0 && $totalVentas > 0 ? ($totalExternos / $totalVentas) * 100 : 0, 2) }}
                                            %
                                        </td>
                                        <td class="border-r px-6 py-3 font-bold">
                                            {{ number_format(collect($reporteExternos)->sum('gastos_adm'), 2) }}
                                        </td>
                                        <td class="border-r px-6 py-3 font-bold">
                                            {{ number_format(collect($reporteExternos)->sum('costos_totales'), 2) }}
                                        </td>
                                        <td class="border-r px-6 py-3 font-bold">
                                            {{ number_format(collect($reporteExternos)->sum('rentabilidad'), 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif


        <!-- TABLA REPORTE TALLERES -->
        @if ($asistir)
            <div class="bg-gray-200 px-8 py-4 rounded-xl w-full mt-4">
                <!-- Tabla semanales -->
                @if ($semanales->count())
                    <table class="min-w-full border text-center text-sm font-light rounded-xl overflow-hidden">
                        <thead class="font-medium dark:border-neutral-500">
                            <tr>
                                <th scope="col" class="text-center text-indigo-600 text-xl font-bold mb-4"
                                    colspan="7">
                                    {{ 'Resumen Talleres ' . $fechaInicio . ' al ' . $fechaFin }}
                                </th>
                            </tr>
                            <tr>
                                <th scope="col" class="text-center text-indigo-600 text-xl font-bold mb-4"
                                    colspan="7">
                                    {{ 'Talleres Semanales ' }}
                                </th>
                            </tr>
                            <tr class="bg-indigo-200">
                                <th scope="col" class="border-r px-6 py-4">#</th>
                                <th scope="col" class="border-r px-6 py-4">Taller</th>
                                <th scope="col" class="border-r px-6 py-4">Ingresos Totales</th>
                                <th scope="col" class="border-r px-6 py-4">% Ventas</th>
                                <th scope="col" class="border-r px-6 py-4">Gastos Administrativos</th>
                                <th scope="col" class="border-r px-6 py-4">Costos Totales</th>
                                <th scope="col" class="border-r px-6 py-4">Rentabilidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($semanales as $data)
                                <tr class="bg-orange-200 hover:bg-orange-300">
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ $data['taller'] }}
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ number_format($data['total'], 2) }}
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ number_format($data['porcentaje'], 2) }} %
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ number_format($data['gastos_adm'], 2) }}
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ number_format($data['costos_totales'], 2) }}
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ number_format($data['rentabilidad'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-green-200">
                                <td colspan="2" class="border-r px-6 py-3 font-bold text-right">
                                    CIERRE SEMANALES:
                                </td>
                                <td class="border-r px-6 py-3 font-bold">
                                    {{ number_format($semanales->sum('total'), 2) }}
                                </td>
                                <td class="border-r px-6 py-3 font-bold">
                                    {{ number_format(
                                        $semanales->sum('total') > 0 && $totalVentas > 0 ? ($semanales->sum('total') / $totalVentas) * 100 : 0,
                                        2,
                                    ) }}
                                    %
                                </td>
                                <td class="border-r px-6 py-3 font-bold">
                                    {{ number_format($semanales->sum('gastos_adm'), 2) }}
                                </td>
                                <td class="border-r px-6 py-3 font-bold">
                                    {{ number_format($semanales->sum('costos_totales'), 2) }}
                                </td>
                                <td class="border-r px-6 py-3 font-bold">
                                    {{ number_format($semanales->sum('rentabilidad'), 2) }}
                                </td>
                            </tr>

                        </tbody>
                    </table>
                @endif
                <!-- Tabla diarios -->
                @if ($diarios->count())
                    <table class="min-w-full border text-center text-sm font-light rounded-xl overflow-hidden">
                        <thead class="font-medium dark:border-neutral-500">
                            <tr>
                                <th scope="col" class="text-center text-indigo-600 text-xl font-bold mb-4"
                                    colspan="7">
                                    {{ 'Talleres Diarios ' }}
                                </th>
                            </tr>
                            <tr class="bg-indigo-200">
                                <th scope="col" class="border-r px-6 py-4">#</th>
                                <th scope="col" class="border-r px-6 py-4">Taller</th>
                                <th scope="col" class="border-r px-6 py-4">Ingresos Totales</th>
                                <th scope="col" class="border-r px-6 py-4">% Ventas</th>
                                <th scope="col" class="border-r px-6 py-4">Gastos Administrativos</th>
                                <th scope="col" class="border-r px-6 py-4">Costos Totales</th>
                                <th scope="col" class="border-r px-6 py-4">Rentabilidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($diarios as $data)
                                <tr class="bg-orange-200 hover:bg-orange-300">
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ $data['taller'] }}
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ number_format($data['total'], 2) }}
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ number_format($data['porcentaje'], 2) }} %
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ number_format($data['gastos_adm'], 2) }}
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ number_format($data['costos_totales'], 2) }}
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ number_format($data['rentabilidad'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-green-200">
                                <td colspan="2" class="border-r px-6 py-3 font-bold text-right">
                                    CIERRE DIARIOS:
                                </td>
                                <td class="border-r px-6 py-3 font-bold">
                                    {{ number_format($diarios->sum('total'), 2) }}
                                </td>
                                <td class="border-r px-6 py-3 font-bold">
                                    {{ number_format(
                                        $diarios->sum('total') > 0 && $totalVentas > 0 ? ($diarios->sum('total') / $totalVentas) * 100 : 0,
                                        2,
                                    ) }}
                                    %
                                </td>
                                <td class="border-r px-6 py-3 font-bold">
                                    {{ number_format($diarios->sum('gastos_adm'), 2) }}
                                </td>
                                <td class="border-r px-6 py-3 font-bold">
                                    {{ number_format($diarios->sum('costos_totales'), 2) }}
                                </td>
                                <td class="border-r px-6 py-3 font-bold">
                                    {{ number_format($diarios->sum('rentabilidad'), 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                @endif
                <!-- Tabla otros -->
                @if ($otros->count())
                    <table class="min-w-full border text-center text-sm font-light rounded-xl overflow-hidden">
                        <thead class="font-medium dark:border-neutral-500">
                            <tr>
                                <th colspan="7" class="text-indigo-600 text-xl font-bold">
                                    Talleres sin Frecuencia
                                </th>
                            </tr>
                            <tr class="bg-indigo-200">
                                <th scope="col" class="border-r px-6 py-4">#</th>
                                <th scope="col" class="border-r px-6 py-4">Taller</th>
                                <th scope="col" class="border-r px-6 py-4">Ingresos Totales</th>
                                <th scope="col" class="border-r px-6 py-4">% Ventas</th>
                                <th scope="col" class="border-r px-6 py-4">Gastos Administrativos</th>
                                <th scope="col" class="border-r px-6 py-4">Costos Totales</th>
                                <th scope="col" class="border-r px-6 py-4">Rentabilidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($otros as $data)
                                <tr class="bg-orange-200 hover:bg-orange-300">
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ $data['taller'] }}
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ number_format($data['total'], 2) }}
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ number_format($data['porcentaje'], 2) }} %
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ number_format($data['gastos_adm'], 2) }}
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ number_format($data['costos_totales'], 2) }}
                                    </td>
                                    <td class="whitespace-nowrap border-r px-6 py-3">
                                        {{ number_format($data['rentabilidad'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-green-200">
                                <td colspan="2" class="border-r px-6 py-3 font-bold text-right">
                                    CIERRE OTROS:
                                </td>
                                <td class="border-r px-6 py-3 font-bold">
                                    {{ number_format($otros->sum('total'), 2) }}
                                </td>
                                <td class="border-r px-6 py-3 font-bold">
                                    {{ number_format(
                                        $otros->sum('total') > 0 && $totalVentas > 0 ? ($otros->sum('total') / $totalVentas) * 100 : 0,
                                        2,
                                    ) }}
                                    %
                                </td>
                                <td class="border-r px-6 py-3 font-bold">
                                    {{ number_format($otros->sum('gastos_adm'), 2) }}
                                </td>
                                <td class="border-r px-6 py-3 font-bold">
                                    {{ number_format($otros->sum('costos_totales'), 2) }}
                                </td>
                                <td class="border-r px-6 py-3 font-bold">
                                    {{ number_format($otros->sum('rentabilidad'), 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            </div>            

        @endif

        <!-- Resumen Consolidado-->
        @if($resumenFinal)
            <div class="bg-indigo-200 shadow-2xl rounded-xl p-6 w-full mt-6 text-white">
                <h3 class="text-center text-xl font-bold mb-4 uppercase tracking-wider text-indigo-600">
                    Resumen Consolidado de Rentabilidad
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-center">
                        <thead>
                            <tr class="border-b border-indigo-700 text-indigo-600 uppercase text-xs">
                                <th class="px-4 py-2">Ingresos Totales</th>
                                <th class="px-4 py-2">Gastos Adm.</th>
                                <th class="px-4 py-2">Costos Operativos + Laborales</th>
                                <th class="px-4 py-2">Utilidad Neta</th>
                                <th class="px-4 py-2">Margen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="text-2xl font-bold">
                                <td class="px-4 py-4">S/ {{ number_format($resumenFinal['ingresos'], 2) }}</td>
                                <td class="px-4 py-4 text-orange-500">S/ {{ number_format($resumenFinal['gastos_adm'], 2) }}</td>
                                <td class="px-4 py-4 text-red-500">S/ {{ number_format($resumenFinal['costos_totales'], 2) }}</td>
                                <td class="px-4 py-4 text-green-500">S/ {{ number_format($resumenFinal['rentabilidad'], 2) }}</td>
                                <td class="px-4 py-4">
                                    <span class="bg-green-600 text-white px-3 py-1 rounded-full text-lg">
                                        {{ $resumenFinal['margen'] }} %
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-center text-indigo-600 text-xs italic">
                    * La rentabilidad es el resultado de: Ingresos - (Gastos Adm + Costos Totales por grupo)
                </div>
            </div>
        @endif


    </div>
</div>
