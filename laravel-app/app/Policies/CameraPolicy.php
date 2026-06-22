<?php

namespace App\Policies;

use App\Models\Camera;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CameraPolicy
{
    private const VIEW_ROLES = ['viewer', 'editor', 'manager'];

    private const UPDATE_ROLES = ['editor', 'manager'];

    private const DELETE_ROLES = ['manager'];

    private const SHARE_ROLES = ['manager'];

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Camera $camera): bool|Response
    {
        return $this->ownsCamera($user, $camera) || $this->hasSharedRole($user, $camera, self::VIEW_ROLES)
            ? true
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Camera $camera): bool|Response
    {
        return $this->ownsCamera($user, $camera) || $this->hasSharedRole($user, $camera, self::UPDATE_ROLES)
            ? true
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Camera $camera): bool|Response
    {
        return $this->ownsCamera($user, $camera) || $this->hasSharedRole($user, $camera, self::DELETE_ROLES)
            ? true
            : Response::denyAsNotFound();
    }

    public function share(User $user, Camera $camera): bool|Response
    {
        return $this->ownsCamera($user, $camera) || $this->hasSharedRole($user, $camera, self::SHARE_ROLES)
            ? true
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Camera $camera): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Camera $camera): bool
    {
        return false;
    }

    private function ownsCamera(User $user, Camera $camera): bool
    {
        return $camera->user_id === $user->id;
    }

    /**
     * @param  array<int, string>  $roles
     */
    private function hasSharedRole(User $user, Camera $camera, array $roles): bool
    {
        return $camera->sharedUsers()
            ->whereKey($user->id)
            ->wherePivotIn('role', $roles)
            ->exists();
    }
}
