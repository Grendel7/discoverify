<?php

namespace App\Http\Controllers;

use App\Channel;
use App\UserChannel;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class UserChannelsController extends Controller
{
    public function index(Request $request)
    {
        return view('userChannels.index', [
            'userChannels' => $request->user()->channels()->with('channel')->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', UserChannel::class);

        return view('userChannels.create', [
            'playlists' => $request->user()->playlists,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', UserChannel::class);

        $this->validate($request, [
            'channel_url' => ['required', 'url', 'regex:/https\:\/\/www\.youtube\.com\/(channel|user)\/[\w-]+/'],
            'playlists.*' => [
                'in:'.$request->user()->playlists()->pluck('id')->implode(','),
            ],
        ]);

        $matches = [];
        preg_match('/^https:\/\/www\.youtube\.com\/channel\/([\w-]+)$/', $request->get('channel_url'), $matches);
        if ($matches) {
            $channelId = $matches[1];
        } else {
            $page = (string) (new Client())->get($request->get('channel_url'))->getBody();
            $matches = [];
            preg_match('/https:\/\/www\.youtube\.com\/feeds\/videos.xml\?channel_id=([\w-]+)/', $page, $matches);
            $channelId = $matches[1];
        }

        $channel = Channel::where('youtube_id', $channelId)->first();

        if (!$channel) {
            $channel = new Channel();
            $channel->youtube_id = $channelId;
            $xml = simplexml_load_string(file_get_contents($channel->getFeedUrl()));
            $channel->name = $xml->title;
            $channel->saveOrFail();
        } elseif ($request->user()->channels()->pluck('channel_id')->contains($channel->id)) {
            redirect()->back()->withErrors([
                'channel_url' => 'You already have this channel in your account.',
            ]);
        }

        $userChannel = new UserChannel();
        $userChannel->channel_id = $channel->id;
        $userChannel->user_id = $request->user()->id;
        $userChannel->saveOrFail();
        $userChannel->playlists()->sync($request->get('playlists'));

        return redirect()->route('userChannels.index')->with('success', 'The channel has been added!');
    }

    public function show(UserChannel $userChannel)
    {
        $this->authorize('view', $userChannel);

        $tracks = $userChannel->channel->tracks()->orderBy('created_at', 'desc')->paginate(25);

        return view('userChanels.show', [
            'userChannel' => $userChannel,
            'tracks' => $tracks,
        ]);
    }

    public function edit(UserChannel $userChannel, Request $request)
    {
        $this->authorize('update', $userChannel);

        return view('userChannels.edit', [
            'userChannel' => $userChannel,
            'playlists' => $request->user()->playlists,
        ]);
    }

    public function update(UserChannel $userChannel, Request $request)
    {
        $this->validate($request, [
            'playlists.*' => 'in:'.$request->user()->playlists()->pluck('id')->implode(','),
        ]);

        $userChannel->playlists()->sync($request->get('playlists'));

        return redirect()->route('userChannels.index')->with('success', 'The channel has been updated!');
    }

    public function destroy(UserChannel $userChannel, Request $request)
    {
        $this->authorize('delete', $userChannel);
        $userChannel->delete();

        return redirect()->route('userChannels.index')->with('success', 'The channel has been deleted!');
    }
}