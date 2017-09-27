<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function channels()
    {
        return $this->belongsToMany(UserChannel::class);
    }
}