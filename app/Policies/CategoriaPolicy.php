<?php

namespace App\Policies;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoriaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('Ver Categoria');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Categoria $categoria): bool
    {
        return $user->hasPermissionTo('Ver Categoria');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Criar Categoria');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Categoria $categoria): bool
    {
        return $user->hasPermissionTo('Atualizar Categoria');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Categoria $categoria): bool
    {
        return $user->hasPermissionTo('Deletar Categoria');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Categoria $categoria)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Categoria $categoria)
    {
        //
    }
}
