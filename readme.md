# Discoverify

Discover music on Spotify using curated YouTube lists.

## What is Discoverify?

There are many channels on YouTube who publish amazing music you won't have heard before. You could follow these channels on YouTube and check YouTube every day for new music, but YouTube is a video platform, not a music streaming service. Ideally, you'd take the curated lists from YouTube and add them all to Spotify where you can easily listen to them.

That's what Discoverify does for you. Simply login with your Spotify account, paste in the URL of the YouTube channel you want to follow and select a Spotify playlist you want to add the songs to. Discoverify will watch the channels for new music, find the Spotify links in their descriptions and add the songs to your playlists.

## System Requirements

- PHP 5.6.4 or newer
- A HTTP server with PHP support (Apache, Nginx, Caddy)
- Composer
- A supported database: MySQL, PostgreSQL or SQLite

## Installation

Discoverify is built using Laravel. See the [installation instructions for Laravel](https://laravel.com/docs/5.4/installation).

### Get your Spotify credentials

To use Discoverify and login with Spotify, you need to get Spotify OAuth credentials.

1. Create your application: https://developer.spotify.com/my-applications/
2. Add the redirect URL http://your.domain.com/path/to/discoverify/login/spotify
3. Add the credentials to the `.env` file.


### Setup the schedule runner

Add a cron job which executes the command `php artisan schedule:run`. The schedule runner is configured to synchronize music every hour.