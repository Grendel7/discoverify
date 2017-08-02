<?php

namespace App\Http\Controllers;

use App\Channel;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Spatie\Regex\Regex;

class ChannelsController extends Controller
{
    public function __construct()
    {
        $this->middleware('access_token');
    }

    public function index(Request $request)
    {
        return view('channels.index', [
            'channels' => $request->user()->channels,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Channel::class);

        return view('channels.create', [
            'playlists' => app('spotify')->getMyPlaylists(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Channel::class);

        $this->validate($request, [
            'channel_url' => ['required', 'url', 'regex:/https\:\/\/www\.youtube\.com\/(channel|user)\/[\w-]+/'],
            'playlist_id' => [
                'required',
                'in:'.implode(',', array_map(function ($item) {
                    return $item->id;
                }, app('spotify')->getUserPlaylists($request->user()->remote_id)->items)),
            ],
        ]);

        $match = Regex::match('/^https:\/\/www\.youtube\.com\/channel\/([\w-]+)$/', $request->get('channel_url'));
        if ($match->hasMatch()) {
            $channelId = $match->group(1);
        } else {
            $page = (string) (new Client())->get($request->get('channel_url'))->getBody();
            $channelId = Regex::match('/https:\/\/www\.youtube\.com\/feeds\/videos.xml\?channel_id=([\w-]+)/', $page)->group(1);
        }

        $channel = new Channel();
        $channel->channel_id = $channelId;
        $channel->playlist_id = $request->get('playlist_id');
        $channel->user_id = $request->user()->id;

        $feedContents = file_get_contents($channel->getFeedUrl());
        $xml = simplexml_load_string($feedContents);

        $channel->name = $request->get('name') ? $request->get('name') : $xml->title;
        $channel->saveOrFail();

        return redirect()->route('channels.index')->with('success', 'The channel has been added!');
    }

    public function show(Channel $channel)
    {
        $this->authorize('view', $channel);

        $tracks = $channel->tracks()->orderBy('created_at', 'desc')->paginate(25);

        return view('channels.show', [
            'channel' => $channel,
            'tracks' => $tracks,
        ]);
    }

    public function edit(Channel $channel)
    {
        $this->authorize('update', $channel);

        $playlist = app('spotify')->getUserPlaylist($channel->user->remote_id, $channel->playlist_id);

        return view('channels.edit', [
            'channel' => $channel,
            'playlist' => $playlist,
        ]);
    }

    public function update(Channel $channel, Request $request)
    {
        $this->authorize('update', $channel);

        $this->validate($request, [
            'name' => 'required',
        ]);

        $channel->name = $request->get('name');
        $channel->saveOrFail();

        return redirect()->route('channels.index')->with('success', 'The channel has been updated!');
    }

    public function destroy(Channel $channel, Request $request)
    {
        $this->authorize('delete', $channel);
        $channel->delete();

        return redirect()->route('channels.index')->with('success', 'The channel has been deleted!');
    }
}