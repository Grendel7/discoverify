<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use SpotifyWebAPI\Session;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'access_token', 'refresh_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'token_expires_at' => 'datetime',
    ];

    public function channels()
    {
        return $this->hasMany(UserChannel::class);
    }

    public function playlists()
    {
        return $this->hasMany(Playlist::class);
    }

    public function assertValidAccessToken()
    {
        if ($this->token_expires_at->lt(Carbon::now())) {
            $spotifySession = new Session(
                config('services.spotify.client_id'),
                config('services.spotify.client_secret'),
                config('services.spotify.redirect_url')
            );

            $spotifySession->refreshAccessToken($this->refresh_token);
            $this->access_token = $spotifySession->getAccessToken();
            $this->token_expires_at = Carbon::createFromTimestamp($spotifySession->getTokenExpiration());
            $this->saveOrFail();
        }
    }
}
