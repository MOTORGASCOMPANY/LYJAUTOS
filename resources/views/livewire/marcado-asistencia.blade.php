<div class="relative min-h-screen flex items-center justify-center bg-gray-900 overflow-hidden font-sans">
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1504384308090-c894fdcc538d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80"
            class="object-cover w-full h-full opacity-40">
        <div class="absolute inset-0 bg-gradient-to-r from-black to-transparent"></div>
    </div>

    <div class="relative z-10 w-full max-w-4xl px-12 flex flex-col md:flex-row items-center">
        <div class="w-full md:w-1/2 text-white">
            <!-- Fecha y hora -->
            <div class="mb-6">
                <div class="flex justify-center md:justify-center">
                    <p class="text-lg font-light opacity-80">{{ now()->translatedFormat('l j \d\e F \d\e\l Y') }}</p>
                </div>
                <div wire:poll.1000ms class="flex justify-center md:justify-center text-4xl font-bold tracking-tighter">
                    {{ now()->format('H : i : s A') }}
                </div>
            </div>
            <!-- Título -->
            <div class="mb-10">
                <div class="flex justify-center mb-6">
                    <img src="{{ asset('images/images/mtg2.png') }}"
                        class="h-20 object-contain drop-shadow-[0_0_15px_rgba(255,255,255,0.2)]">
                </div>

                <div class="flex items-center justify-between">
                    <h1 class="text-5xl font-black italic uppercase leading-none text-white">
                        Sistema de registro <br> <span class="text-orange-500">de asistencia</span>
                    </h1>
                    <div class="relative flex items-center group">
                        <a href="{{ route('login') }}"
                            class="relative z-10 flex items-center justify-center w-16 h-16 bg-white/10 border-2 border-white/20 rounded-full transition-all duration-300 hover:bg-orange-600 hover:border-orange-500 hover:scale-110 shadow-lg group-active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-8 w-8 text-white group-hover:translate-x-1 transition-transform" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                            <span class="absolute inset-0 rounded-full bg-white/20 animate-ping group-hover:hidden"></span>
                        </a>
                        <span class="absolute left-full ml-4 px-4 py-2 bg-orange-600 text-white text-xs font-black uppercase tracking-[0.2em] rounded-lg opacity-0 translate-x-2 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-0 whitespace-nowrap shadow-[0_0_20px_rgba(234,88,12,0.5)] pointer-events-none">
                            Acceso Login
                            <span class="absolute top-1/2 -left-1 -translate-y-1/2 w-3 h-3 bg-orange-600 rotate-45"></span>
                        </span>
                    </div>
                </div>

                <p class="mt-4 text-gray-400 font-medium italic border-l-4 border-orange-500 pl-3">
                    Coloque su DNI para registrar su marcación (Entrada / Salida Automática).
                </p>
            </div>

            {{-- 
            @if ($isDeviceAuthorized)
                <!-- Tipo de marcado -->
                <div class="grid grid-cols-2 gap-4 mb-10">
                    <x-jet-label for="tipo_entrada" class="relative cursor-pointer group">
                        <x-jet-input type="radio" id="tipo_entrada" wire:model="tipo" value="Entrada" class="hidden peer" />
                        <div class="flex flex-col items-center justify-center p-6 bg-black/40 border-2 border-white/10 rounded-2xl transition-all duration-300 peer-checked:border-orange-500 peer-checked:bg-orange-600/20 group-hover:bg-black/60 shadow-xl">
                            <div
                                class="w-4 h-4 rounded-full border-2 border-white/50 mb-3 peer-checked:bg-orange-500 peer-checked:border-orange-500 transition-colors duration-300 {{ $tipo == 'Entrada' ? 'bg-orange-500 border-orange-500' : '' }}">
                            </div>
                            <span
                                class="text-xl font-black italic uppercase tracking-tighter transition-colors duration-300 {{ $tipo == 'Entrada' ? 'text-orange-500' : 'text-gray-400' }}">
                                Entrada
                            </span>
                            <span class="text-[10px] text-gray-500 uppercase tracking-widest mt-1">Ingreso Laboral</span>
                        </div>
                    </x-jet-label>

                    <x-jet-label for="tipo_salida" class="relative cursor-pointer group">
                        <x-jet-input type="radio" id="tipo_salida" wire:model="tipo" value="Salida" class="hidden peer" />
                        <div class="flex flex-col items-center justify-center p-6 bg-black/40 border-2 border-white/10 rounded-2xl transition-all duration-300 peer-checked:border-white peer-checked:bg-white/10 group-hover:bg-black/60 shadow-xl">
                            <div class="w-4 h-4 rounded-full border-2 border-white/50 mb-3 transition-colors duration-300 {{ $tipo == 'Salida' ? 'bg-white border-white' : '' }}">
                            </div>
                            <span class="text-xl font-black italic uppercase tracking-tighter transition-colors duration-300 {{ $tipo == 'Salida' ? 'text-white' : 'text-gray-400' }}">
                                Salida
                            </span>
                            <span class="text-[10px] text-gray-500 uppercase tracking-widest mt-1">Fin de Jornada</span>
                        </div>
                    </x-jet-label>
                </div>
                <!-- Formulario DNI y Registrar -->
                <form wire:submit.prevent="registrarMarcado" class="space-y-6">
                    <div class="flex items-center space-x-4">
                        <div class="flex-1">
                            <x-jet-input type="text" id="dni_input" wire:model.defer="dni" placeholder="DNI (8 dígitos)"
                                class="w-full text-gray-900 font-black focus:border-orange-500 focus:ring-orange-500 text-center"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)" maxlength="8"
                                autofocus />
                        </div>
                        <x-jet-button type="submit"
                            class="bg-orange-600 hover:bg-orange-700 active:bg-orange-800 text-white font-black px-10 py-4 text-lg rounded-xl tracking-tighter shadow-2xl transition-all transform active:scale-95">
                            {{ __('Registrar') }}
                        </x-jet-button>
                    </div>
                    <x-jet-input-error for="dni"
                        class="text-orange-500 text-xl font-black italic bg-black/40 p-2 rounded inline-block" />
                </form>
            @else
                <div class="bg-red-600/20 border-2 border-red-500 p-6 rounded-2xl text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-500 mx-auto mb-3" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <h2 class="text-xl font-black text-white uppercase italic">Dispositivo No Autorizado</h2>
                    <p class="text-gray-400 text-xs">Esta estación de trabajo no está habilitada para marcar asistencia.
                        Contacte con administración.</p>
                </div>
            @endif
            --}}
            @if ($isDeviceAuthorized)
                <!-- Formulario DNI y Registrar -->
                <form wire:submit.prevent="registrarMarcado" class="space-y-6">
                    <div class="flex items-center space-x-4">
                        <div class="flex-1">
                            <x-jet-input type="text" id="dni_input" wire:model.defer="dni" placeholder="DNI (8 dígitos)"
                                class="w-full text-gray-900 font-black focus:border-orange-500 focus:ring-orange-500 text-center"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)" maxlength="8"
                                autofocus />
                        </div>
                        <x-jet-button type="submit"
                            class="bg-orange-600 hover:bg-orange-700 active:bg-orange-800 text-white font-black px-10 py-4 text-lg rounded-xl tracking-tighter shadow-2xl transition-all transform active:scale-95">
                            {{ __('Registrar') }}
                        </x-jet-button>
                    </div>
                    <x-jet-input-error for="dni"
                        class="text-orange-500 text-xl font-black italic bg-black/40 p-2 rounded inline-block" />
                </form>
            @else
                <div class="bg-red-600/20 border-2 border-red-500 p-6 rounded-2xl text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-500 mx-auto mb-3" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <h2 class="text-xl font-black text-white uppercase italic">Dispositivo No Autorizado</h2>
                    <p class="text-gray-400 text-xs">Esta estación de trabajo no está habilitada para marcar asistencia.
                        Contacte con administración.</p>
                </div>
            @endif

        </div>       

        <div class="hidden md:block w-1/2">
        </div>       
        
    </div>

    <script>
        document.addEventListener('livewire:load', function() {
            const input = document.getElementById('dni_input');

            // 1. Intentar leer el token del dispositivo
            let token = localStorage.getItem('mtg_device_token');
            if (token) {
                // Enviarlo al componente para validar
                @this.checkDevice(token);
            }

            // foco automatico
            input.focus();
            document.addEventListener('click', (e) => {
                // Mantenemos foco si no se hace click en los radios o botón
                if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'BUTTON' && e.target.tagName !==
                    'LABEL') {
                    input.focus();
                }
            });
        });

        // Escuchar cuando el admin autoriza para guardar el token localmente
        window.addEventListener('device-authorized', event => {
            localStorage.setItem('mtg_device_token', event.detail.token);
            alert('¡Esta laptop ha sido autorizada con éxito!');
            location.reload();
        });
    </script>
</div>
