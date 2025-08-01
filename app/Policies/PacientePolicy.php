<?php

namespace App\Policies;

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PacientePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('Ver Paciente');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Paciente $paciente): bool
    {
        return $user->hasPermissionTo('Ver Paciente');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Criar Paciente');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Paciente $paciente): bool
    {
        return $user->hasPermissionTo('Atualizar Paciente');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Paciente $paciente): bool
    {
       return $user->hasPermissionTo('Deletar Paciente');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Paciente $paciente)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Paciente $paciente)
    {
        //
    }
}
