<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-white">{{ __('Log in to VMHub') }}</h1>
        <p class="mt-2 text-sm text-slate-400">{{ __('Review cameras, recordings, and processor activity from your monitoring dashboard.') }}</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-700 bg-slate-950 text-white shadow-sm focus:ring-cyan-400 focus:ring-offset-slate-950" name="remember">
                <span class="ms-2 text-sm text-slate-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-6 flex items-center justify-between gap-4">
            @if (Route::has('password.request'))
                <a class="text-sm text-slate-400 hover:text-slate-100" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <p class="mt-6 text-sm text-slate-400">
            {{ __('New to VMHub?') }}
            <a href="{{ route('register') }}" class="font-medium text-white hover:text-slate-200">{{ __('Create an account') }}</a>
        </p>
    </form>
</x-guest-layout>
