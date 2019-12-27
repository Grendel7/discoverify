<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect the user to the Spotify authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('spotify')->scopes([
            'playlist-read-private', 'playlist-modify-public', 'playlist-modify-private', 'user-read-email',
        ])->redirect();
    }

    /**
     * Obtain the user information from Spotify.
     *
     * @return Response
     */
    public function handleProviderCallback()
    {
        $spotifyUser = Socialite::driver('spotify')->user();

        $user = User::where('social_driver', 'spotify')->where('remote_id', $spotifyUser->getId())->first();

        if (!$user) {
            $user = new User();
            $user->remote_id = $spotifyUser->getId();
            $user->social_driver = 'spotify';
            $user->password = bcrypt(Str::random());
        }

        $user->email = $spotifyUser->getEmail();
        $user->name = $spotifyUser->getId();
        $user->access_token = $spotifyUser->token;
        $user->refresh_token = $spotifyUser->refreshToken;
        $user->token_expires_at = Carbon::now()->addSeconds($spotifyUser->expiresIn);

        $user->saveOrFail();

        Auth::login($user, true);

        return redirect($this->redirectTo);
    }
}
