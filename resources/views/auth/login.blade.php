<x-guest-layout>

    <div class="min-h-screen bg-slate-50 text-slate-900 flex justify-center items-center font-sans">
        <div class="max-w-screen-xl m-0 sm:m-10 bg-white shadow-2xl sm:rounded-2xl flex justify-center flex-1 overflow-hidden min-h-[600px]">

            <div class="flex-1 bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-center hidden lg:flex items-center justify-center p-12 relative">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(99,102,241,0.08),transparent_50%)]"></div>
                
                <div class="max-w-md w-full z-10 space-y-6">
                    <div class="inline-flex p-3 bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 shadow-inner">
                        <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h2 class="text-2xl font-bold text-white tracking-tight">Bienvenido a la nueva plataforma</h2>
                        <p class="text-sm text-indigo-200/70 leading-relaxed px-4">
                            Accede de forma rápida y segura a tu panel de control para gestionar todas tus operaciones y procesos centralizados.
                        </p>
                    </div>
                </div>
            </div>

            <div class="lg:w-1/2 xl:w-5/12 p-8 sm:p-12 flex flex-col justify-center">
                <div class="flex justify-center mb-6 transition-transform duration-300 hover:scale-105 ">
                    <img src="{{ asset('images/logo.png') }}" width="200" alt="Logo Empresa" class="object-contain" />
                </div>                

                <div class="flex flex-col items-center">
                    <div class="w-full flex-1 mt-4">
                        
                        <div class="max-w-xs mx-auto mb-4">
                            <x-jet-validation-errors class="mb-4 text-sm text-red-600" />

                            @if (session('status'))
                                <div class="mb-4 font-medium text-sm text-emerald-600 bg-emerald-50 p-3 rounded-lg border border-emerald-200">
                                    {{ session('status') }}
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col items-center">
                            <button type="button"
                                class="w-full max-w-xs font-semibold shadow-sm rounded-xl py-3.5 bg-slate-100 text-slate-700 flex items-center justify-center transition-all duration-300 ease-in-out focus:outline-none hover:bg-slate-200 hover:shadow-md focus:ring-2 focus:ring-indigo-500/20">
                                <div class="bg-white p-1.5 rounded-full flex items-center justify-center shadow-sm">
                                    <svg class="w-4 h-4" viewBox="0 0 533.5 544.3">
                                        <path d="M533.5 278.4c0-18.5-1.5-37.1-4.7-55.3H272.1v104.8h147c-6.1 33.8-25.7 63.7-54.4 82.7v68h87.7c51.5-47.4 81.1-117.4 81.1-200.2z" fill="#4285f4" />
                                        <path d="M272.1 544.3c73.4 0 135.3-24.1 180.4-65.7l-87.7-68c-24.4 16.6-55.9 26-92.6 26-71 0-131.2-47.9-152.8-112.3H28.9v70.1c46.2 91.9 140.3 149.9 243.2 149.9z" fill="#34a853" />
                                        <path d="M119.3 324.3c-11.4-33.8-11.4-70.4 0-104.2V150H28.9c-38.6 76.9-38.6 167.5 0 244.4l90.4-70.1z" fill="#fbbc04" />
                                        <path d="M272.1 107.7c38.8-.6 76.3 14 104.4 40.8l77.7-77.7C405 24.6 339.7-.8 272.1 0 169.2 0 75.1 58 28.9 150l90.4 70.1c21.5-64.5 81.8-112.4 152.8-112.4z" fill="#ea4335" />
                                    </svg>
                                </div>
                                <span class="ml-3 text-sm tracking-wide">Iniciar sesión con Google</span>
                            </button>
                        </div>

                        <div class="my-8 border-b border-slate-200 text-center relative max-w-xs mx-auto">
                            <div class="leading-none px-3 inline-block text-xs text-slate-400 uppercase tracking-widest bg-white absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                O CONTINÚA CON
                            </div>
                        </div>

                        <div class="mx-auto max-w-xs">
                            <form method="POST" action="{{ route('login') }}" id="formLogin" class="space-y-4">
                                @csrf

                                <div>
                                    <input id="email" name="email" :value="old('email')" required autofocus
                                        class="w-full px-5 py-3.5 rounded-xl font-medium bg-slate-50 border border-slate-200 placeholder-slate-400 text-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all"
                                        type="email" placeholder="Correo electrónico" />
                                </div>

                                <div>
                                    <input id="password" name="password" required
                                        class="w-full px-5 py-3.5 rounded-xl font-medium bg-slate-50 border border-slate-200 placeholder-slate-400 text-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all"
                                        type="password" placeholder="Contraseña" />
                                </div>

                                <div class="flex items-center justify-between text-xs py-1">
                                    <label for="remember_me" class="flex items-center cursor-pointer select-none">
                                        <input type="checkbox" id="remember_me" name="remember" 
                                            class="w-4 h-4 text-indigo-600 focus:ring-indigo-500/20 border-slate-300 rounded transition-all">
                                        <span class="ml-2 text-slate-500 font-medium hover:text-slate-700">{{ __('Recordar sesión') }}</span>
                                    </label>

                                    @if (Route::has('password.request'))
                                        <a class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline transition-colors"
                                            href="{{ route('password.request') }}">
                                            {{ __('¿Olvidaste tu contraseña?') }}
                                        </a>
                                    @endif
                                </div>

                                <button id="loginButton" type="submit"
                                    class="w-full py-4 rounded-xl tracking-wide font-semibold bg-indigo-600 text-white hover:bg-indigo-700 active:bg-indigo-800 transition-all duration-200 shadow-lg shadow-indigo-600/20 hover:shadow-indigo-600/30 flex items-center justify-center focus:outline-none focus:ring-4 focus:ring-indigo-500/30">
                                    <svg class="w-5 h-5 opacity-90" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                                        <polyline points="10 17 15 12 10 7" />
                                        <line x1="15" y1="12" x2="3" y2="12" />
                                    </svg>
                                    <span class="ml-2 text-sm uppercase tracking-wider">Ingresar al Sistema</span>
                                </button>
                            </form>

                            <p class="mt-8 text-[11px] text-slate-400 text-center leading-relaxed">
                                Al ingresar aceptas nuestros 
                                <a href="#" class="text-slate-500 hover:text-slate-700 underline font-medium">Términos de Servicio</a> 
                                y la 
                                <a href="#" class="text-slate-500 hover:text-slate-700 underline font-medium">Política de Privacidad</a>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            

        </div>
    </div>

    @push('js')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const loginButton = document.querySelector("#loginButton");
            const formLogin = document.querySelector("#formLogin");
            
            if (formLogin && loginButton) {
                formLogin.addEventListener("submit", function() {
                    // Deshabilita el botón al procesar el formulario para evitar doble clic
                    loginButton.disabled = true;
                    loginButton.classList.add('opacity-50', 'cursor-not-allowed');
                });
            }
        });
    </script>
    @endpush

    <!-- source:https://codepen.io/owaiswiz/pen/jOPvEPB -->
    {{-- 
    <div class="min-h-screen bg-gray-100 text-gray-900 flex justify-center">
        <div class="max-w-screen-xl m-0 sm:m-10 bg-white shadow sm:rounded-lg flex justify-center flex-1">

            <div class="lg:w-1/2 xl:w-5/12 p-6 sm:p-12">
                <div class="flex justify-center animate-bounce">
                    <img src="{{ asset('images/logo.png') }}" width="220" height="120" />
                </div>                

                <div class="flex flex-col items-center">
                    <div class="w-full flex-1 mt-8">
                        <!-- Inicie con google -->
                        <div class="flex flex-col items-center">
                            <button
                                class="w-full max-w-xs font-bold shadow-sm rounded-lg py-3 bg-orange-100 text-gray-800 flex items-center justify-center transition-all duration-300 ease-in-out focus:outline-none hover:shadow focus:shadow-sm focus:shadow-outline">
                                <div class="bg-white p-2 rounded-full">
                                    <svg class="w-4" viewBox="0 0 533.5 544.3">
                                        <path
                                            d="M533.5 278.4c0-18.5-1.5-37.1-4.7-55.3H272.1v104.8h147c-6.1 33.8-25.7 63.7-54.4 82.7v68h87.7c51.5-47.4 81.1-117.4 81.1-200.2z"
                                            fill="#4285f4" />
                                        <path
                                            d="M272.1 544.3c73.4 0 135.3-24.1 180.4-65.7l-87.7-68c-24.4 16.6-55.9 26-92.6 26-71 0-131.2-47.9-152.8-112.3H28.9v70.1c46.2 91.9 140.3 149.9 243.2 149.9z"
                                            fill="#34a853" />
                                        <path
                                            d="M119.3 324.3c-11.4-33.8-11.4-70.4 0-104.2V150H28.9c-38.6 76.9-38.6 167.5 0 244.4l90.4-70.1z"
                                            fill="#fbbc04" />
                                        <path
                                            d="M272.1 107.7c38.8-.6 76.3 14 104.4 40.8l77.7-77.7C405 24.6 339.7-.8 272.1 0 169.2 0 75.1 58 28.9 150l90.4 70.1c21.5-64.5 81.8-112.4 152.8-112.4z"
                                            fill="#ea4335" />
                                    </svg>
                                </div>
                                <span class="ml-4">
                                    Iniciar sesión con Google
                                </span>
                            </button>
                        </div>
                        <!-- Inicie con correo electronico -->
                        <div class="my-12 border-b text-center">
                            <div
                                class="leading-none px-2 inline-block text-sm text-gray-600 tracking-wide font-medium bg-white transform translate-y-1/2">
                                Inicie sesión con correo electrónico
                            </div>
                        </div>

                        <div class="mx-auto max-w-xs">
                            <form method="POST" action="{{ route('login') }}" id="formLogin">
                                @csrf

                                <input id="email" name="email" :value="old('email')" required autofocus
                                    class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-orange-300 focus:ring focus:ring-orange-200 focus:bg-white"
                                    type="email" placeholder="Correo" />
                                <input id="password" name="password" required
                                    class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-orange-300 focus:ring focus:ring-orange-200 focus:bg-white mt-5"
                                    type="password" placeholder="Contraseña" />

                                <div class="flex items-center justify-between mt-4">
                                    <label for="remember_me" class="flex items-center">
                                        <input type="checkbox" id="remember_me" name="remember" class="text-orange-500 focus:ring-orange-400 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-600">{{ __('Recordar') }}</span>
                                    </label>

                                    @if (Route::has('password.request'))
                                        <a class="underline text-sm text-gray-600 hover:text-gray-900"
                                            href="{{ route('password.request') }}">
                                            {{ __('olvidaste tu contraseña?') }}
                                        </a>
                                    @endif
                                </div>
                                <button id="loginButton" type="submit"
                                    class="mt-5 tracking-wide font-semibold bg-orange-400 text-white-500 w-full py-4 rounded-lg hover:bg-orange-700 transition-all duration-300 ease-in-out flex items-center justify-center focus:shadow-outline focus:outline-none">
                                    <svg class="w-6 h-6 -ml-2" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                                        <circle cx="8.5" cy="7" r="4" />
                                        <path d="M20 8v6M23 11h-6" />
                                    </svg>
                                    <span class="ml-2">
                                        Iniciar sesión
                                    </span>
                                </button>
                            </form>

                            <p class="mt-6 text-xs text-gray-600 text-center">
                                I agree to abide by Cartesian Kinetics
                                <a href="#" class="border-b border-gray-500 border-dotted">
                                    Terms of Service
                                </a>
                                and its
                                <a href="#" class="border-b border-gray-500 border-dotted">
                                    Privacy Policy
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex-1 bg-orange-100 text-center hidden lg:flex">
                <div class="m-12 xl:m-16 w-full bg-contain bg-center bg-no-repeat">
                    <img src="{{ asset('images/background2.svg') }}" />
                </div>
            </div>
        </div>
    </div>
    --}}

    {{--    
    <x-jet-authentication-card>
        <x-slot name="logo">
            <x-jet-authentication-card-logo />
        </x-slot>

        <x-jet-validation-errors class="mb-4" />

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-orange-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="formLogin">
            @csrf

            <div>
                <x-jet-label for="email" value="{{ __('Correo') }}" />
                <x-jet-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <div class="mt-4">
                <x-jet-label for="password" value="{{ __('Contraseña') }}" />
                <x-jet-input id="password" class="block mt-1 w-full" type="password" name="password" required />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-jet-checkbox id="remember_me" name="remember" />
                    <span class="ml-2 text-sm text-gray-600">{{ __('Recordar') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                        {{ __('olvidaste tu contraseña?') }}
                    </a>
                @endif

                <x-jet-button class="ml-4"  id="loginButton"  >
                    {{ __('Ingresar') }}
                </x-jet-button>
            </div>
        </form>
    </x-jet-authentication-card>   
   
    @push('js')
    <script >
        
        const login=document.querySelector("#loginButton");
        const form=document.querySelector("#formLogin");
        console.log(login);
        /*
         window.onload = function(){
            login.disabled=false;
        
        }*/
        
        login.addEventListener("click", function(event) {
            login.disabled=true;
            form.submit();            
        });       
    </script>
    @endpush
    --}}
    
</x-guest-layout>
