<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('settings.update') }}" class="p-6">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="timezone" :value="__('Timezone')" />
                        <select
                            id="timezone"
                            name="timezone"
                            class="mt-1 block w-full rounded-md border-slate-300 bg-white text-slate-900 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            required
                        >
                            @foreach ($timezones as $timezone)
                                <option value="{{ $timezone }}" @selected(old('timezone', $settings->timezone) === $timezone)>
                                    {{ $timezone }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
                    </div>

                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Current app time: :time', ['time' => $currentTime->format('Y-m-d H:i')]) }}
                    </p>

                    <div class="mt-6 flex items-center justify-end">
                        <x-primary-button>{{ __('Save Settings') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
