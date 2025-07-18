<?php

namespace App\Policies;

use App\Models\Doenca;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DoencaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('Ver Doenca');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Doenca $doenca): bool
    {
        return $user->hasPermissionTo('Ver Doenca');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Criar Doenca');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Doenca $doenca): bool
    {
        return $user->hasPermissionTo('Atualizar Doenca');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Doenca $doenca): bool
    {
        return $user->hasPermissionTo('Deletar Doenca');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Doenca $doenca)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Doenca $doenca)
    {
        //
    }
}
