<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserChannel extends Model
{
    public static function boot()
    {
        parent::boot();

        self::deleting(function (UserChannel $userChannel) {
            $userChannel->playlists()->detach();
        });

        self::deleted(function (UserChannel $userChannel) {
            $channel = $userChannel->channel;

            if ($channel->userChannels()->count() == 0) {
                $channel->delete();
            }
        });
    }

    public function playlists()
    {
        return $this->belongsToMany(Playlist::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
}