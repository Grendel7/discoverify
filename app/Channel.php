<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $dates = ['created_at', 'updated_at'];

    public static function boot()
    {
        parent::boot();

        self::deleting(function (Channel $channel) {
            $channel->tracks()->delete();
        });
    }

    public function tracks()
    {
        return $this->hasMany(Track::class);
    }

    public function userChannels()
    {
        return $this->hasMany(UserChannel::class);
    }

    public function getFeedUrl()
    {
        return 'https://www.youtube.com/feeds/videos.xml?channel_id='.$this->youtube_id;
    }
}