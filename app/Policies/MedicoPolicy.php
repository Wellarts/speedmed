<?php

namespace App\Policies;

use App\Models\Medico;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MedicoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('Ver Médico');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Medico $medico): bool
    {
        return $user->hasPermissionTo('Ver Médico');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Criar Médico');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Medico $medico): bool
    {
        return $user->hasPermissionTo('Atualizar Médico');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Medico $medico): bool
    {
        return $user->hasPermissionTo('Deletar Médico');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Medico $medico)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Medico $medico)
    {
        //
    }
}
