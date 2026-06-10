<x-guest-layout>
    <div class="text-center space-y-6">
        <h1 class="text-3xl font-bold text-gray-900">Studio Management System</h1>
        <p class="text-gray-600">Create and manage your studio from one platform.</p>
        <div class="flex justify-center gap-4">
            <a href="{{ route('institutes.register') }}" class="px-4 py-2 bg-indigo-600 text-white rounded">Create Studio</a>
            <a href="{{ route('login') }}" class="px-4 py-2 border rounded">Login</a>
        </