<?php

namespace App\Policies;

use App\Models\Camera;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CameraPolicy
{
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
        return $this->ownsCamera($user, $camera);
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
        return $this->ownsCamera($user, $camera);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Camera $camera): bool|Response
    {
        return $this->ownsCamera($user, $camera);
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

    private function ownsCamera(User $user, Camera $camera): bool|Response
    {
        return $camera->user_id === $user->id
            ? true
            : Response::denyAsNotFound();
    }
}
