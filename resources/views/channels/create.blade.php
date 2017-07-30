@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        New Channel
                    </div>

                    <div class="panel-body">
                        @if($errors && $errors->any())
                            @foreach($errors->all() as $error)
                                <div class="alert alert-danger" role="alert">{{ $error }}</div>
                            @endforeach
                        @endif

                        <form method="post" action="{{ route('channels.store') }}">
                            <div class="form-group">
                                <label for="channel_url">YouTube Channel URL</label>
                                <input name="channel_url" id="channel_url" class="form-control" type="text" required>
                            </div>

                            <div class="form-group">
                                <label for="name">Channel Name (optional)</label>
                                <input name="name" id="name" class="form-control" type="text">
                            </div>

                            <div class="form-group">
                                <label for="playlist_id">Playlist</label>
                                <select name="playlist_id" id="playlist_id" class="form-control">
                                    @foreach ($playlists->items as $playlist)
                                        <option value="{{ $playlist->id }}">{{ $playlist->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button class="btn btn-success">Create Channel</button>
                            {{ csrf_field() }}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
