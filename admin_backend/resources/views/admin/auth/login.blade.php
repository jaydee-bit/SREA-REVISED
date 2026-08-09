<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SREA Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white rounded-2xl shadow-lg p-10 w-full max-w-sm">

        {{-- Logo: replace public/images/srea-logo.png with your own file, same filename --}}
        <div class="flex justify-center mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="SREA Logo" class="h-20">
        </div>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600 text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf

            <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500">

            <label class="block text-sm font-medium text-gray-600 mb-1">Password</label>
            <input type="password" name="password" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500">

            <button type="submit"
                    class="w-full bg-blue-700 text-white font-semibold py-2 rounded-lg hover:bg-blue-800 transition">
                Login
            </button>
        </form>

    </div>

</body>
</html>