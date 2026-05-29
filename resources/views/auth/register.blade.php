<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('dark') === 'true', showPassword: false, showConfirmPassword: false }"
    x-init="$watch('darkMode', val => localStorage.setItem('dark', val)); document.documentElement.classList.toggle('dark', darkMode)">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 transition-colors duration-500">

    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-8 transform transition duration-500 hover:scale-[1.01]">

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

            <!-- Heading -->
            <h2 class="text-3xl font-extrabold mb-2 text-center text-gray-800 dark:text-white">Create Account</h2>
            <p class="text-center text-gray-500 dark:text-gray-400 mb-6">Register as a student</p>

            <!-- Form -->
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600
                                  focus:outline-none focus:ring-2 focus:ring-blue-400
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400">
                    @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600
                                  focus:outline-none focus:ring-2 focus:ring-blue-400
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400">
                    @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Password</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required autocomplete="new-password"
                            class="w-full px-4 py-2 pr-14 rounded-lg border border-gray-300 dark:border-gray-600
                                  focus:outline-none focus:ring-2 focus:ring-blue-400
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400">

                        <button type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-xs font-bold text-gray-500 transition hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400"
                            :aria-label="showPassword ? 'Hide password' : 'Show password'">
                            <span x-show="!showPassword">Show</span>
                            <span x-show="showPassword">Hide</span>
                        </button>
                    </div>
                    @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Confirm Password</label>
                    <div class="relative">
                        <input :type="showConfirmPassword ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                            class="w-full px-4 py-2 pr-14 rounded-lg border border-gray-300 dark:border-gray-600
                                  focus:outline-none focus:ring-2 focus:ring-blue-400
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400">

                        <button type="button"
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-xs font-bold text-gray-500 transition hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400"
                            :aria-label="showConfirmPassword ? 'Hide password confirmation' : 'Show password confirmation'">
                            <span x-show="!showConfirmPassword">Show</span>
                            <span x-show="showConfirmPassword">Hide</span>
                        </button>
                    </div>
                    @error('password_confirmation') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Role (fixed to student) -->
                <div>
                    <label for="role" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Registering as</label>
                    <select id="role" name="role"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600
                                   focus:outline-none focus:ring-2 focus:ring-blue-400
                                   bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="student" selected>Student</option>
                    </select>
                    @error('role') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Submit -->
                <button type="submit"
                    class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg shadow-md
                               hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                    Register
                </button>
            </form>

            <!-- Divider -->
            <div class="my-6 flex items-center">
                <hr class="flex-grow border-gray-300 dark:border-gray-600">
                <span class="px-3 text-sm text-gray-500 dark:text-gray-400">or</span>
                <hr class="flex-grow border-gray-300 dark:border-gray-600">
            </div>

            <!-- Already registered -->
            <p class="text-center text-gray-600 dark:text-gray-300 text-sm">
                Already have an account?
                <a href="{{ route('login') }}" class="text-blue-600 dark:text-blue-400 font-medium hover:underline">
                    Login
                </a>
            </p>
        </div>
    </div>

</body>

</html>
