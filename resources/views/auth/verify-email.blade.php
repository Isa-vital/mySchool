<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Verify your email</h1>
        <p class="mt-2 text-sm text-gray-500">Click the link we sent to your email to verify your account. Didn't receive it? We can send another.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
    <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-sm text-emerald-700">
        {{ __('A new verification link has been sent to your email address.') }}
    </div>
    @endif

    <div class="flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="py-2.5 text-sm">
                {{ __('Resend link') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-emerald-600 transition-colors">
                {{ __('Log out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
</x-guest-layout>