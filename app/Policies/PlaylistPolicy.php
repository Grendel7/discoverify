<?php

namespace App\Policies;

use App\Models\Playlist;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlaylistPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Playlist $playlist)
    {
        return $playlist->user_id == $user->id;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Playlist $playlist)
    {
        return $this->view($user, $playlist);
    }

    public function delete(User $user, Playlist $playlist)
    {
        return $this->view($user, $playlist);
    }
}
