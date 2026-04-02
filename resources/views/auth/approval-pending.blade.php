<x-guest-layout>
    <div class="text-center">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 mb-6">
            <svg class="h-8 w-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-2">Account Pending Approval</h2>

        <p class="text-gray-600 text-sm mb-6">
            Your email has been verified. Your account is now awaiting approval from the system administrator.
            You will be able to access the dashboard once your account is approved.
        </p>

        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
            <p class="text-amber-800 text-sm font-medium">
                Please contact the administrator if you need immediate access.
            </p>
        </div>

        <div class="flex items-center justify-center gap-4">
            <a href="{{ url('/') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">&larr; Back to home</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 font-medium">Log out</button>
            </form>
        </div>
    </div>
</x-guest-layout>
