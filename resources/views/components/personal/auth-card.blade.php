{{--
    Reusable authentication card component
    Used for login, register, and other auth-related pages
    Provides consistent styling with shadow, border, and dark mode support
--}}
<div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 p-8 sm:p-10">
    {{ $slot }}
</div>
