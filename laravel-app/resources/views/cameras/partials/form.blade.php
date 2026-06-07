@php($camera = $camera ?? null)

<div>
    <x-input-label for="name" :value="__('Name')" />
    <x-text-input
        id="name"
        name="name"
        type="text"
        class="mt-1 block w-full"
        :value="old('name', $camera?->name)"
        required
        autofocus
    />
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
</div>

<div class="mt-4">
    <x-input-label for="stream_url" :value="__('Stream URL')" />
    <x-text-input
        id="stream_url"
        name="stream_url"
        type="url"
        class="mt-1 block w-full"
        :value="old('stream_url', $camera?->stream_url)"
        required
    />
    <x-input-error class="mt-2" :messages="$errors->get('stream_url')" />
</div>

<div class="mt-4">
    <x-input-label for="location" :value="__('Location')" />
    <x-text-input
        id="location"
        name="location"
        type="text"
        class="mt-1 block w-full"
        :value="old('location', $camera?->location)"
    />
    <x-input-error class="mt-2" :messages="$errors->get('location')" />
</div>

<div class="mt-4">
    <label for="is_active" class="inline-flex items-center">
        <input
            id="is_active"
            name="is_active"
            type="checkbox"
            value="1"
            @checked(old('is_active', $camera?->is_active ?? true))
            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
        >
        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Active') }}</span>
    </label>
    <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
</div>
