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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tracks()
    {
        return $this->hasMany(Track::class);
    }

    public function getFeedUrl()
    {
        return 'https://www.youtube.com/feeds/videos.xml?channel_id='.$this->channel_id;
    }
}