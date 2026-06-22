<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCameraShareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('share', $this->route('camera')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'exists:users,email',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $user = User::where('email', $value)->first();
                    $camera = $this->route('camera');

                    if (! $user || ! $camera) {
                        return;
                    }

                    if ($user->id === $camera->user_id) {
                        $fail(__('The camera owner already has full access.'));
                    }

                    if ($user->id === $this->user()?->id) {
                        $fail(__('You already have access to this camera.'));
                    }

                    if ($camera->sharedUsers()->whereKey($user->id)->exists()) {
                        $fail(__('This camera is already shared with that user.'));
                    }
                },
            ],
            'role' => ['required', Rule::in(['viewer', 'editor', 'manager'])],
        ];
    }
}
