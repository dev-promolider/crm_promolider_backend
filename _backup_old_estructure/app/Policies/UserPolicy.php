<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->accountType->id == 1;
    }

    /**
     * Determine whether the user can view another user's data.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function viewUserData(User $user, User $model)
    {
        // El usuario puede ver sus propios datos o ser admin
        return $user->id === $model->id || $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can view payments of another user.
     *
     * @param  \App\Models\User  $user
     * @param  int  $userId
     * @return mixed
     */
    public function viewPayments(User $user, $userId)
    {
        return $user->id == $userId || $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can view sales of another user.
     *
     * @param  \App\Models\User  $user
     * @param  int  $userId
     * @return mixed
     */
    public function viewSales(User $user, $userId)
    {
        return $user->id == $userId || $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function view(User $user, User $model)
    {
        
        return $user->accountType->id == 1  || $user->id == $model->id;

        /*
          $authorization= false;
        if($user->accountType->id == 1){
            $authorization =  true;
        }elseif($user->id == $model->id){
            $authorization = true;
        }else{
            $id = $user->id;
            $users = User::whereRaw("FIND_IN_SET(id, GET_CHILD_NODE(${id}))")->get()->filter(function ($key) use($model) {
                return $key->user  == $model;
            });
            if()

        }
         */

    }
    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function update(User $user, User $model)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function delete(User $user, User $model)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function restore(User $user, User $model)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function forceDelete(User $user, User $model)
    {
        //
    }
}
