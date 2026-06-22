<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCameraShareRequest;
use App\Http\Requests\UpdateCameraShareRequest;
use App\Models\Camera;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class CameraShareController extends Controller
{
    public function store(StoreCameraShareRequest $request, Camera $camera): RedirectResponse
    {
        $user = User::where('email', $request->validated('email'))->firstOrFail();

        $camera->sharedUsers()->attach($user->id, [
            'role' => $request->validated('role'),
        ]);

        return redirect()
            ->route('cameras.show', $camera)
            ->with('status', 'Camera shared successfully.');
    }

    public function update(UpdateCameraShareRequest $request, Camera $camera, User $user): RedirectResponse
    {
        abort_unless($camera->sharedUsers()->whereKey($user->id)->exists(), 404);

        $camera->sharedUsers()->updateExistingPivot($user->id, [
            'role' => $request->validated('role'),
        ]);

        return redirect()
            ->route('cameras.show', $camera)
            ->with('status', 'Camera sharing role updated successfully.');
    }

    public function destroy(Camera $camera, User $user): RedirectResponse
    {
        $this->authorize('share', $camera);

        abort_unless($camera->sharedUsers()->whereKey($user->id)->exists(), 404);

        $camera->sharedUsers()->detach($user->id);

        return redirect()
            ->route('cameras.show', $camera)
            ->with('status', 'Camera access removed successfully.');
    }
}
