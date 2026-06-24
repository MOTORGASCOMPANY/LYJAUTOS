<div class="mt-16 flex justify-center">
    <div class="bg-white p-6 rounded-xl shadow max-w-3xl w-full">
        <!-- TÍTULO -->
        <h2 class="text-xl font-bold text-indigo-600 mb-1 flex items-center gap-2">
            <i class="fa-solid fa-calendar-check"></i>
            Periodo contable
        </h2>
        <p class="text-sm text-gray-500 mb-6">
            Selecciona el año y mes para registrar o gestionar los gastos administrativos.
        </p>
        <!-- FORMULARIO -->
        <form wire:submit.prevent="generar" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <x-jet-label value="Año" />
                <x-jet-input type="number" wire:model.defer="anio" class="mt-1 w-full" />
                @error('anio')
                    <span class="text-xs text-red-500">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <x-jet-label value="Mes" />
                <x-jet-input type="number" wire:model.defer="mes" class="mt-1 w-full" />
                @error('mes')
                    <span class="text-xs text-red-500">{{ $message }}</span>
                @enderror
            </div>
            <div class="sm:col-span-2 flex justify-end pt-2">
                <x-jet-button type="submit">
                    Gestionar periodo
                </x-jet-button>
            </div>
        </form>
    </div>
</div>
