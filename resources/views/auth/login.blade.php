<!DOCTYPE html>
<html x-data="{ darkMode: localStorage.getItem('dark') === 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('dark', val)); 
              document.documentElement.classList.toggle('dark', darkMode)">


<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">


    <!-- Scripts - The key to making Tailwind work -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 bg-gray-100 dark:bg-gray-900 antialiased h-full">


    <div class="min-h-screen flex items-center justify-center 
            bg-gradient-to-br from-blue-100 via-white to-blue-50 
            dark:bg-gradient-to-br dark:from-gray-800 dark:via-gray-900 dark:to-gray-800
            px-4">
        <!-- Main Login Card Container -->
        <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md transform transition duration-500 hover:scale-[1.01] dark:bg-gray-800">

            <!-- Logo / Icon -->

            <!-- Dark Mode Toggle -->
            <div class="flex justify-end">
                <button
                    @click="darkMode = !darkMode; document.documentElement.classList.toggle('dark', darkMode)"
                    class="p-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-7.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 5a7 7 0 100 14a7 7 0 000-14z" />
                    </svg>
                    <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                    </svg>
                </button>
            </div>
            <!-- Header Text -->
            <h2 class="text-3xl font-extrabold mb-2 text-center text-gray-800 dark:text-gray-100">Welcome Back</h2>
            <p class="text-center text-gray-500 mb-6 dark:text-gray-300">Login to access your account</p>

            <!-- Login Form -->
            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Email Input Field -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1 dark:text-gray-200">Email</label>
                    <input type="email" id="email" name="email" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition placeholder-gray-400 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="you@example.com">
                </div>

                <!-- Password Input Field -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1 dark:text-gray-200">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition placeholder-gray-400 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="••••••••">
                </div>

                <!-- Forgot Password Link -->
                <div class="flex items-center justify-end">
                    <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-700 transition">
                        Forgot password?
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                    Login
                </button>
            </form>

            <!-- Divider -->
            <div class="my-6 flex items-center">
                <hr class="flex-grow border-gray-300 dark:border-gray-600">
                <span class="px-3 text-sm text-gray-500 dark:text-gray-400">or</span>
                <hr class="flex-grow border-gray-300 dark:border-gray-600">
            </div>

            <!-- Register Link -->
            <p class="text-center text-gray-600 text-sm dark:text-gray-300">
                Sign up as a student?
                <a href="{{ route('register') }}" class="text-blue-600 font-medium hover:text-blue-700">Sign up</a>
            </p>
        </div>
    </div>

</body>

</html>