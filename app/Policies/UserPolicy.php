<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;


    public function view(User $authUser, User $user)
    {
        return $authUser->id === $user->id;
    }


    public function update(User $authUser, User $user)
    {
        return $authUser->id === $user->id;
    }

    public function delete(User $authUser, User $user)
    {
        return $authUser->id === $user->id;
    }
}
