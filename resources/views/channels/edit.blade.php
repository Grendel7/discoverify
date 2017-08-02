@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Edit Channel: {{ $channel->name }}
                    </div>

                    <div class="panel-body">
                        @if($errors && $errors->any())
                            @foreach($errors->all() as $error)
                                <div class="alert alert-danger" role="alert">{{ $error }}</div>
                            @endforeach
                        @endif

                        <form method="post" action="{{ route('channels.update', ['channel' => $channel]) }}">
                            <div class="form-group">
                                <label for="channel_url">YouTube Channel URL</label>
                                <input id="channel_url" class="form-control" type="text" disabled
                                       value="https://www.youtube.com/channels/{{ $channel->channel_id }}">
                            </div>

                            <div class="form-group">
                                <label for="name">Channel Name</label>
                                <input name="name" id="name" class="form-control" type="text"
                                       value="{{ old('name', $channel->name) }}" required>
                            </div>

                            <div class="form-group">
                                <label for="playlist_id">Playlist</label>
                                <input type="text" class="form-control" id="playlist_id" disabled value="{{ $playlist->name }}" />
                            </div>

                            <button class="btn btn-success">Update Channel</button>
                            {{ csrf_field() }}
                            {{ method_field('PUT') }}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
