<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tidak Ditemukan - Sistem Absensi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-purple-50 to-blue-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
            <div class="mb-6">
                <div class="mx-auto w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                Halaman Tidak Ditemukan
            </h1>

            <p class="text-gray-600 mb-6">
                Maaf, halaman yang Anda cari tidak ditemukan atau telah dipindahkan.
            </p>

            <div class="bg-gray-50 rounded-lg p-3 mb-6">
                <p class="text-sm text-gray-500">
                    Kode Error: <span class="font-mono font-semibold text-gray-700">404</span>
                </p>
            </div>

            <a 
                href="{{ route('login') }}" 
                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 inline-block"
            >
                Kembali ke Halaman Login
            </a>
        </div>
    </div>
</body>
</html>
