<x-app-layout>
    <div class="min-h-screen bg-gray-50/60 dark:bg-gray-900 p-6 sm:p-8">
        <div class="flex flex-col md:flex-row gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Create Student
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Create a new student user.
                </p>
            </div>
        </div>

        <form action="{{ route('admin.students.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Name
                    </label>
                    <input type="text" name="name" id="name" required
                        class="mt-1 block w-full rounded-lg border-gray-300
                               dark:border-gray-600 dark:bg-gray-700
                               dark:text-white focus:border-indigo-500
                               focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Email
                    </label>
                    <input type="email" name="email" id="email" required
                        class="mt-1 block w-full rounded-lg border-gray-300
                               dark:border-gray-600 dark:bg-gray-700
                               dark:text-white focus:border-indigo-500
                               focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Password
                    </label>
                    <input type="password" name="password" id="password" required
                        class="mt-1 block w-full rounded-lg border-gray-300
                               dark:border-gray-600 dark:bg-gray-700
                               dark:text-white focus:border-indigo-500
                               focus:ring-indigo-500 sm:text-sm">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Confirm Password
                    </label>
                    <input type="password" name="password_confirmation" required
                        class="mt-1 block w-full rounded-lg border-gray-300
                               dark:border-gray-600 dark:bg-gray-700
                               dark:text-white focus:border-indigo-500
                               focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Phone Number
                    </label>
                    <input type="text" name="phone_number" id="phone_number"
                        class="mt-1 block w-full rounded-lg border-gray-300
                               dark:border-gray-600 dark:bg-gray-700
                               dark:text-white focus:border-indigo-500
                               focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg
                           text-xs font-semibold text-gray-700 dark:text-gray-300
                           bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <i class="bx bx-plus"></i>
                    Create Student
                </button>
            </div>
        </form>
    </div>
</x-app-layout>