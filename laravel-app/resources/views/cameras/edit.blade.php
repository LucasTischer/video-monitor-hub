<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Edit Camera') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-slate-900 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('cameras.update', $camera) }}" class="p-6">
                    @csrf
                    @method('PATCH')

                    @include('cameras.partials.form', ['camera' => $camera])

                    <div class="mt-6 flex items-center justify-end gap-4">
                        <a href="{{ route('cameras.index') }}" class="text-sm text-slate-400 underline hover:text-slate-100">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button>{{ __('Update Camera') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
