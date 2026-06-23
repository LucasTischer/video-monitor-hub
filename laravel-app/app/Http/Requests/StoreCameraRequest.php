<?php

namespace App\Http\Requests;

use App\Models\Camera;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCameraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'stream_url' => ['required', 'url', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'motion_detection_enabled' => ['boolean'],
            'record_after_motion_seconds' => ['required', 'integer', 'min:1', 'max:60'],
            'pre_motion_buffer_seconds' => ['required', 'integer', 'min:0', 'max:30'],
            'monitoring_starts_at' => ['nullable', 'required_with:monitoring_ends_at', 'date_format:H:i'],
            'monitoring_ends_at' => ['nullable', 'required_with:monitoring_starts_at', 'date_format:H:i'],
            'recording_retention_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'motion_detection_enabled' => $this->boolean('motion_detection_enabled'),
            'record_after_motion_seconds' => $this->filled('record_after_motion_seconds')
                ? $this->input('record_after_motion_seconds')
                : Camera::DEFAULT_RECORD_AFTER_MOTION_SECONDS,
            'pre_motion_buffer_seconds' => $this->filled('pre_motion_buffer_seconds')
                ? $this->input('pre_motion_buffer_seconds')
                : Camera::DEFAULT_PRE_MOTION_BUFFER_SECONDS,
            'monitoring_starts_at' => $this->input('monitoring_starts_at') === ''
                ? null
                : $this->input('monitoring_starts_at'),
            'monitoring_ends_at' => $this->input('monitoring_ends_at') === ''
                ? null
                : $this->input('monitoring_ends_at'),
            'recording_retention_days' => $this->input('recording_retention_days') === ''
                ? null
                : $this->input('recording_retention_days'),
        ]);
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (
                    $this->filled('monitoring_starts_at')
                    && $this->filled('monitoring_ends_at')
                    && $this->input('monitoring_starts_at') === $this->input('monitoring_ends_at')
                ) {
                    $validator->errors()->add(
                        'monitoring_ends_at',
                        'The monitoring end time must be different from the start time.'
                    );
                }
            },
        ];
    }
}
