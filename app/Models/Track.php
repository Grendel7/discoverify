<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $dates = ['created_at', 'updated_at'];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
}
