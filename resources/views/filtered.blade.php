<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>rumio - {{ $category->name }}</title>
    <meta name="description" content="rumio - Semua tercatat & transparan">
    <meta name="author" content="Eryan Fauzan">

    <link rel="icon" type="image/jpeg" href="{{ asset('storage/rumio.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @include('welcome.style')
</head>

<body class="font-poppins bg-pattern min-h-screen">
    <!-- Decorative Shapes -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <!-- Main Container -->
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-2xl">
            <!-- Include Header -->
            @include('welcome.form-header')

            <!-- Include Form -->
            @include('welcome.form')

            <!-- Include Footer -->
            @include('welcome.footer')
        </div>
    </div>

    <!-- Include Script -->
    @include('welcome.script')

</body>

</html>
