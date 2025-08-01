<?php

namespace App\Policies;

use App\Models\Especialidade;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EspecialidadePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('Ver Especialidade');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Especialidade $especialidade): bool
    {
        return $user->hasPermissionTo('Ver Especialidade');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Criar Especialidade');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Especialidade $especialidade): bool
    {
        return $user->hasPermissionTo('Atualizar Especialidade');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Especialidade $especialidade): bool
    {
        return $user->hasPermissionTo('Deletar Especialidade');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Especialidade $especialidade)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Especialidade $especialidade)
    {
        //
    }
}
