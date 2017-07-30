<?php

namespace App\Http\Controllers;

use App\Channel;
use Illuminate\Http\Request;
use Spatie\Regex\Regex;

class ChannelsController extends Controller
{

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
            'playlists' => app('spotify')->getUserPlaylists($request->user()->remote_id),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Channel::class);

        $this->validate($request, [
            'channel_url' => ['required', 'url', 'regex:/https\:\/\/www\.youtube\.com\/channel\/[\w-]+/'],
            'playlist_id' => [
                'required',
                'in:'.implode(',', array_map(function ($item) {
                    return $item->id;
                }, app('spotify')->getUserPlaylists($request->user()->remote_id)->items)),
            ],
        ]);

        $match = Regex::match('/^https:\/\/www\.youtube\.com\/channel\/([\w-]+)$/', $request->get('channel_url'));
        $channelId = $match->group(1);
        $feedUrl = 'https://www.youtube.com/feeds/videos.xml?channel_id='.$channelId;

        $feedContents = file_get_contents($feedUrl);
        $xml = simplexml_load_string($feedContents);

        $channel = new Channel();
        $channel->playlist_id = $request->get('playlist_id');
        $channel->user_id = $request->user()->id;
        $channel->feed_url = $feedUrl;
        $channel->name = $request->get('name') ? $request->get('name') : $xml->title;
        $channel->saveOrFail();

        return redirect()->route('channels.index')->with('success', 'The channel has been added!');
    }

    public function destroy(Channel $channel, Request $request)
    {
        $this->authorize('delete', $channel);
        $channel->delete();

        return redirect()->route('channels.index')->with('success', 'The channel has been deleted!');
    }
}