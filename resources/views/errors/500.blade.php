<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terjadi Kesalahan - Sistem Absensi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-red-50 to-pink-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
            <div class="mb-6">
                <div class="mx-auto w-20 h-20 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                Terjadi Kesalahan Server
            </h1>

            <p class="text-gray-600 mb-6">
                Maaf, terjadi kesalahan pada server. Tim kami telah diberitahu dan sedang memperbaikinya.
            </p>

            <div class="bg-gray-50 rounded-lg p-3 mb-6">
                <p class="text-sm text-gray-500">
                    Kode Error: <span class="font-mono font-semibold text-gray-700">500</span>
                </p>
            </div>

            <div class="space-y-3">
                <button 
                    onclick="window.location.reload()" 
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200"
                >
                    Coba Lagi
                </button>

                <a 
                    href="{{ route('login') }}" 
                    class="w-full bg-white hover:bg-gray-50 text-gray-700 font-semibold py-3 px-6 rounded-lg border-2 border-gray-200 transition duration-200 inline-block"
                >
                    Kembali ke Halaman Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
