<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
<div class="bg-white p-8 rounded shadow-md w-96">
    <h2 class="text-2xl font-bold mb-4">Login</h2>
    @if ($errors->any())
        <div class="bg-red-200 text-red-800 p-2 rounded mb-4">
            {{ $errors->first() }}
        </div>
    @endif
    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-semibold mb-1">Username</label>
            <input type="text" name="username" class="border p-2 rounded w-full" required>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-semibold mb-1">Password</label>
            <input type="password" name="password" class="border p-2 rounded w-full" required>
        </div>
        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded w-full hover:bg-blue-700">
            Login
        </button>
    </form>
    <div class="mt-4 text-center">
        <a href="{{ route('register') }}" class="text-blue-500 hover:underline">Register</a>
    </div>
</div>
</body>
</html>
