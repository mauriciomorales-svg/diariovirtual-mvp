<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso — Diario Zona Sur</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Panel Diario Zona Sur</h1>
        <p class="text-sm text-gray-500 mb-6">Ingresa con tu cuenta de administrador.</p>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ url('/login') }}" class="space-y-4" autocomplete="off">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                <input type="text" name="login" value="{{ old('login', 'admin') }}" required autofocus
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <input type="password" name="password" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300">
                <label for="remember" class="ml-2 text-sm text-gray-600">Recordarme</label>
            </div>
            <button type="submit"
                class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition">
                Entrar
            </button>
        </form>
    </div>
</body>
</html>
