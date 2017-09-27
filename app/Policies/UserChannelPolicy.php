<?php

namespace App\Policies;

use App\User;
use App\UserChannel;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserChannelPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the channel.
     *
     * @param  \App\User  $user
     * @param  \App\UserChannel  $channel
     * @return mixed
     */
    public function view(User $user, UserChannel $channel)
    {
        return $user->id == $channel->user_id;
    }

    /**
     * Determine whether the user can create channels.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the channel.
     *
     * @param  \App\User  $user
     * @param  \App\UserChannel  $channel
     * @return mixed
     */
    public function update(User $user, UserChannel $channel)
    {
        return $this->view($user, $channel);
    }

    /**
     * Determine whether the user can delete the channel.
     *
     * @param  \App\User  $user
     * @param  \App\UserChannel  $channel
     * @return mixed
     */
    public function delete(User $user, UserChannel $channel)
    {
        return $this->view($user, $channel);
    }
}