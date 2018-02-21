<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    public static function boot()
    {
        parent::boot();

        self::deleting(function (Playlist $playlist) {
            $playlist->channels()->detach();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function channels()
    {
        return $this->belongsToMany(UserChannel::class);
    }
}