<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}"/>

        <title>L&J AUTOS®</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Scripts 
        <link rel="stylesheet" href="">
        -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
       
    {{--@vite(['resources/css/app.css', 'resources/js/app.js'])--}}
    </head>
    <body>
        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
            <div class="text-xs text-slate-700 -mt-4 float-right">
                Powered by GHFDEV ®
            </div>
        </div>
        @livewireStyles
        @livewireScripts

        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <script>
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Livewire.on('minAlert', function(params) {
                Toast.fire({
                    title: params["titulo"],
                    text: params["mensaje"],
                    icon: params["icono"]
                });
            });

            Livewire.on('CustomAlert', function(params) {
                Swal.fire(
                    params["titulo"],
                    params["mensaje"],
                    params["icono"]
                );
            });
        </script>

        @vite(['resources/css/app.css','resources/js/app.js'])
        @stack('js')
    </body>
   
</html>
