<!--
Product: Metronic is a toolkit of UI components built with Tailwind CSS for developing scalable web applications quickly and efficiently
Version: v9.0.6 modified v9.1.0
Author: Keenthemes
Contact: support@keenthemes.com
Website: https://www.keenthemes.com
Support: https://devs.keenthemes.com
Follow: https://www.twitter.com/keenthemes
License: https://keenthemes.com/metronic/tailwind/docs/getting-started/license
-->
<!DOCTYPE html>
<html class="h-full" data-theme="true" data-theme-mode="light" lang="en">


<head>
    <meta charset="utf-8">
    <title> Rumio - {{ 'Signin' ?? 'Karawang' }} </title>
    <base href="/">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex">
    <meta name="author" content="Eryan Fauzan">
    <meta name="description" content="Rumio - Portal Pengelolaan Iuran Warga Digital untuk Kemudahan dan Efisiensi">
    <link rel="icon" type="image/png" href="{{ asset('storage/logo.jpeg') }}" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('storage/logo.jpeg') }}" />
    <link rel="shortcut icon" href="{{ asset('storage/logo.jpeg') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('storage/logo.jpeg') }}" />

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
    @vite('resources/css/app.scss')
    @stack('head')
</head>

<body class="antialiased flex h-full text-base text-gray-700 dark:bg-coal-500">
    <!-- Theme Mode -->
    <script>
        const defaultThemeMode = 'light'; // light|dark|system
        let themeMode;

        if (document.documentElement) {
            if (localStorage.getItem('theme')) {
                themeMode = localStorage.getItem('theme');
            } else if (document.documentElement.hasAttribute('data-theme-mode')) {
                themeMode = document.documentElement.getAttribute('data-theme-mode');
            } else {
                themeMode = defaultThemeMode;
            }

            if (themeMode === 'system') {
                themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            document.documentElement.classList.add(themeMode);
        }
    </script>
    <!-- End of Theme Mode -->
    <!-- Page -->
    <style>
        .branded-bg {
            background-image: url('{{asset('storage/villa.png') }}');
            background-size: cover;
            /* This makes the image cover the entire container */
            background-position: center;
            /* This centers the image within the container */
            background-repeat: no-repeat;
            /* This prevents the image from repeating */
        }

        .dark .branded-bg {
            background-image: url('{{asset('storage/villa.png') }}');
            background-size: cover;
            /* Same as above, applies for dark theme */
            background-position: center;
            /* Centers the image */
            background-repeat: no-repeat;
            /* Prevents repeating */
        }
    </style>
    <div class="grid lg:grid-cols-2 grow">
        <div class="flex justify-center items-center p-8 lg:p-10 order-2 lg:order-1">
            <div class="card max-w-[370px] w-full">
                <form action="{{ route('login') }}" class="card-body flex flex-col gap-5 p-10" id="sign_in_form"
                    method="post">
                    @csrf
                    <div class="text-center mb-2.5">
                        <a href="h#">
                            <img class="h-[100px] max-w-non mx-auto mb-2.5" src="{{asset('storage/rumio.png') }}" />
                        </a>
                        <h3 class="text-lg font-medium text-gray-900 leading-none mt-2.5 mb-2.5">
                            Akun Pengelola Portal
                        </h3>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="form-label font-normal text-gray-900">
                            Email
                        </label>
                        <input class="input" name="email" placeholder="email@email.com" type="text"
                            value="" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center justify-between gap-1">
                            <label class="form-label font-normal text-gray-900">
                                Password
                                {{-- </label>
        <a class="text-2sm link shrink-0" href="html/demo1/authentication/branded/reset-password/enter-email.html">
         Forgot Password?
        </a> --}}
                        </div>
                        <div class="input" data-toggle-password="true">
                            <input name="password" placeholder="Enter Password" type="password" value="" />
                            <button class="btn btn-icon" data-toggle-password-trigger="true" type="button">
                                <i class="ki-filled ki-eye text-gray-500 toggle-password-active:hidden">
                                </i>
                                <i class="ki-filled ki-eye-slash text-gray-500 hidden toggle-password-active:block">
                                </i>
                            </button>
                        </div>
                    </div>
                    <label class="checkbox-group">
                        <input class="checkbox checkbox-sm" name="remember" type="checkbox" value="1" />
                        <span class="checkbox-label">
                            Remember me
                        </span>
                    </label>
                    @if ($errors->any())
                        <!--begin:: Alert-->
                        <div class="flex flex-col justify-center gap-1 rounded-lg p-4 bg-danger-light">
                            <h3 class="text-md leading-none font-semibold text-gray-900">
                                Attention Required!
                            </h3>
                            @foreach ($errors->all() as $error)
                                <p class="text-gray-700 text-2sm font-normal">
                                    {{ $error }}
                                </p>
                            @endforeach
                        </div>
                        <!--end:: Alert-->
                    @endif
                    <button class="btn btn-primary flex justify-center grow">
                        Sign In
                    </button>
                </form>

            </div>
        </div>
        <div
            class="lg:rounded-xl lg:border lg:border-gray-200 lg:m-5 order-1 lg:order-2 bg-top xxl:bg-center xl:bg-cover bg-no-repeat branded-bg ">
            <div class="flex flex-col p-8 lg:p-16 gap-4">
                {{-- <a href="h#">
                    <img class="h-[45px] max-w-none" src="assets/media/app/al-khumasi-200.png" />
                </a> --}}

                <div class="flex flex-col gap-3">
                    <h3 class="text-2xl font-semibold text-gray-400">
                        Portal Administrator Rumio
                    </h3>
                    <div class="text-base font-medium text-gray-200">
                        Selamat datang di portal<br>
                        <span class="text-gray-400 font-semibold">
                            Masuk dengan akun anda untuk mengakses sistem.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Page -->
    <!-- Scripts -->
    @vite('resources/js/app.js')
    <script src="{{ asset('assets/js/core.bundle.js') }}"></script>
    <!-- End of Scripts -->
</body>

</html>
