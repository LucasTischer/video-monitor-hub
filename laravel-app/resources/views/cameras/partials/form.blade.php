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
    <x-input-label for="recording_retention_days" :value="__('Recording retention')" />
    <select
        id="recording_retention_days"
        name="recording_retention_days"
        class="mt-1 block w-full rounded-md border-slate-300 bg-white text-slate-900 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
    >
        <option value="" @selected(old('recording_retention_days', $camera?->recording_retention_days) === null)>{{ __('Keep forever') }}</option>
        <option value="1" @selected((string) old('recording_retention_days', $camera?->recording_retention_days) === '1')>{{ __('1 day') }}</option>
        <option value="7" @selected((string) old('recording_retention_days', $camera?->recording_retention_days) === '7')>{{ __('7 days') }}</option>
        <option value="30" @selected((string) old('recording_retention_days', $camera?->recording_retention_days) === '30')>{{ __('30 days') }}</option>
        <option value="90" @selected((string) old('recording_retention_days', $camera?->recording_retention_days) === '90')>{{ __('90 days') }}</option>
        <option value="365" @selected((string) old('recording_retention_days', $camera?->recording_retention_days) === '365')>{{ __('365 days') }}</option>
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('recording_retention_days')" />
</div>

<div class="mt-4 grid gap-4 sm:grid-cols-2">
    <div>
        <x-input-label for="record_after_motion_seconds" :value="__('Record after motion stops')" />
        <select
            id="record_after_motion_seconds"
            name="record_after_motion_seconds"
            class="mt-1 block w-full rounded-md border-slate-300 bg-white text-slate-900 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
        >
            @foreach ([1, 2, 5, 10, 30, 60] as $seconds)
                <option
                    value="{{ $seconds }}"
                    @selected((int) old('record_after_motion_seconds', $camera?->record_after_motion_seconds ?? 2) === $seconds)
                >
                    {{ trans_choice(':count second|:count seconds', $seconds) }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('record_after_motion_seconds')" />
    </div>

    <div>
        <x-input-label for="pre_motion_buffer_seconds" :value="__('Pre-motion buffer')" />
        <select
            id="pre_motion_buffer_seconds"
            name="pre_motion_buffer_seconds"
            class="mt-1 block w-full rounded-md border-slate-300 bg-white text-slate-900 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
        >
            @foreach ([0, 1, 2, 5, 10, 30] as $seconds)
                <option
                    value="{{ $seconds }}"
                    @selected((int) old('pre_motion_buffer_seconds', $camera?->pre_motion_buffer_seconds ?? 2) === $seconds)
                >
                    {{ $seconds === 0 ? __('No buffer') : trans_choice(':count second|:count seconds', $seconds) }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('pre_motion_buffer_seconds')" />
    </div>
</div>

<div class="mt-4">
    <x-input-label :value="__('Monitoring window')" />
    <div class="mt-1 grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="monitoring_starts_at" :value="__('Start time')" />
            <x-text-input
                id="monitoring_starts_at"
                name="monitoring_starts_at"
                type="time"
                class="mt-1 block w-full"
                :value="old('monitoring_starts_at', $camera?->monitoring_starts_at ? substr($camera->monitoring_starts_at, 0, 5) : null)"
            />
            <x-input-error class="mt-2" :messages="$errors->get('monitoring_starts_at')" />
        </div>

        <div>
            <x-input-label for="monitoring_ends_at" :value="__('End time')" />
            <x-text-input
                id="monitoring_ends_at"
                name="monitoring_ends_at"
                type="time"
                class="mt-1 block w-full"
                :value="old('monitoring_ends_at', $camera?->monitoring_ends_at ? substr($camera->monitoring_ends_at, 0, 5) : null)"
            />
            <x-input-error class="mt-2" :messages="$errors->get('monitoring_ends_at')" />
        </div>
    </div>
</div>

<div class="mt-4">
    <label for="motion_detection_enabled" class="inline-flex items-center">
        <input
            id="motion_detection_enabled"
            name="motion_detection_enabled"
            type="checkbox"
            value="1"
            @checked(old('motion_detection_enabled', $camera?->motion_detection_enabled ?? true))
            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
        >
        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Motion detection enabled') }}</span>
    </label>
    <x-input-error class="mt-2" :messages="$errors->get('motion_detection_enabled')" />
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
