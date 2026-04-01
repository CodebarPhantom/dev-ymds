<!--
Product: Metronic is a toolkit of UI components built with Tailwind CSS for developing scalable web applications quickly and efficiently
Version: v9.0.6 table modified v9.1.0
Author: Keenthemes
Contact: support@keenthemes.com
Website: https://www.keenthemes.com
Support: https://devs.keenthemes.com
Follow: https://www.twitter.com/keenthemes
License: https://keenthemes.com/metronic/tailwind/docs/getting-started/license
-->
<!doctype html>
<html class="h-full" data-theme="true" data-theme-mode="light" lang="en">

<head>
    <meta charset="utf-8">
    <title> Portal Villa Permata Hijau Karawang - {{ 'Signin' ?? 'Karawang' }} </title>
    <base href="/">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex">
    <meta name="author" content="Eryan Fauzan">
    <meta name="description" content="Portal Villa Permata Hijau Karawang - Portal Pengelolaan Iuran Warga Digital untuk Kemudahan dan Efisiensi">
    <link rel="icon" type="image/png" href="{{ asset('storage/rumio.png') }}" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('storage/rumio.png') }}" />
    <link rel="shortcut icon" href="{{ asset('storage/rumio.png') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('storage/rumio.png') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
    <style>
        #loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            /* Semi-transparent black background */
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-content {
            text-align: center;
            color: #fff;
        }

        .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loading-content p {
            font-size: 1.2em;
            margin: 0;
        }
    </style>
    @vite('resources/css/app.scss')
    @stack('head')
</head>

<body
    class="antialiased flex h-full text-base text-gray-700 [--tw-page-bg:#fefefe] [--tw-page-bg-dark:var(--tw-coal-500)] demo1 sidebar-fixed header-fixed bg-[--tw-page-bg] dark:bg-[--tw-page-bg-dark]"
    data-sticky-header="on">
    <!--begin::Theme mode setup on page load-->
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
    <!--end::Theme mode setup on page load-->

    @yield('main')

    {{-- @include('partials.search-modal') --}}
    <!-- Floating "Go to Top" Button -->
    <button id="goTopButton"
        class="fixed bottom-6 right-6 z-50 p-4 bg-blue-600 text-white rounded-full shadow-xl hover:bg-blue-500 hover:scale-110 ring-4 ring-blue-300 transition-transform duration-300 ease-out"
        onclick="scrollToTop()" aria-label="Scroll to top">
        <!-- Icon (Up arrow) -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
        </svg>
    </button>


    @vite('resources/js/app.js')
    <script src="{{ asset('assets/js/core.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/demo1.js') }}"></script>
    <script>
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Select all buttons with the "markAllReadBtn" class
        document.querySelectorAll('.markAllReadBtn').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();

                // Make the POST request using Axios
                axios.post("#", {}, {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        }
                    })
                    .then(response => {
                        if (!response.data.error) {
                            // Handle UI changes (e.g., hide notifications or update badge)
                            alert("Semua notifikasi telah ditandai sebagai dibaca");
                            location.reload(); // Reload the current tab content
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    </script>
    @stack('javascript')

</body>

</html>
