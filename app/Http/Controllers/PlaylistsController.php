<?php

namespace App\Http\Controllers;

use App\Playlist;
use App\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PlaylistsController extends Controller
{
    public function index(Request $request)
    {
        return view('playlists.index', [
            'playlists' => $request->user()->playlists()->with('channels')->get(),
        ]);
    }

    public function show(Playlist $playlist, Request $request)
    {
        $this->authorize('view', $playlist);

        $tracks = Track::whereHas('channel', function ($query) use ($request, $playlist) {
            return $query->whereHas('userChannels', function ($query) use ($request, $playlist) {
                return $query->where('user_id', $request->user()->id)->whereHas('playlists', function ($query) use ($playlist) {
                    return $query->where('playlist_id', $playlist->id);
                });
            });
        })->orderBy('created_at', 'desc')->paginate(25);

        return view('playlists.show', [
            'playlist' => $playlist,
            'tracks' => $tracks,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Playlist::class);
        $request->user()->assertValidAccessToken();

        return view('playlists.create', [
            'playlists' => app('spotify')->getMyPlaylists(),
            'userChannels' => $request->user()->channels()->with('channel')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Playlist::class);

        $request->user()->assertValidAccessToken();

        $playlists = app('spotify')->getMyPlaylists()->items;

        $this->validate($request, [
            'playlist' => [
                'required',
                'in:'.implode(',', array_map(function ($item) {
                    return $item->id;
                }, $playlists)),
            ],
            'channels.*' => [
                'in:'.$request->user()->playlists()->pluck('id')->implode(','),
            ]
        ]);

        $spotifyPlaylist = Arr::first($playlists, function ($playlist) use ($request) {
            return $playlist->id == $request->get('playlist');
        });

        $playlist = new Playlist();
        $playlist->spotify_id = $request->get('playlist');
        $playlist->name = $spotifyPlaylist->name;
        $playlist->user_id = $request->user()->id;
        $playlist->saveOrFail();
        $playlist->channels()->sync($request->get('channels'));

        return redirect()->route('playlists.index')->with('success', 'The playlist has been added!');
    }

    public function edit(Playlist $playlist, Request $request)
    {
        $this->authorize('update', $playlist);

        return view('playlists.edit', [
            'playlist' => $playlist,
            'userChannels' => $request->user()->channels()->with('channel')->get(),
        ]);
    }

    public function update(Playlist $playlist, Request $request)
    {
        $this->authorize('update', $playlist);

        $this->validate($request, [
            'channels.*' => [
                'in:'.$request->user()->playlists()->pluck('id')->implode(','),
            ]
        ]);

        $playlist->channels()->sync($request->get('channels'));

        return redirect()->route('playlists.index')->with('success', 'The playlist has been updated!');
    }

    public function destroy(Playlist $playlist)
    {
        $this->authorize('delete', $playlist);

        $playlist->delete();

        return redirect()->route('playlists.index')->with('success', 'The playlist has been removed!');
    }
}
