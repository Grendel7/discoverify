@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Add Playlist
                    </div>

                    <div class="panel-body">
                        @if($errors && $errors->any())
                            @foreach($errors->all() as $error)
                                <div class="alert alert-danger" role="alert">{{ $error }}</div>
                            @endforeach
                        @endif

                        <form method="post" action="{{ route('playlists.store') }}">
                            <div class="form-group">
                                <label for="playlist">Playlist</label>
                                <select name="playlist" id="playlist" class="form-control">
                                    @foreach ($playlists->items as $playlist)
                                        <option value="{{ $playlist->id }}">{{ $playlist->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <p>Channels:</p>

                            @foreach ($userChannels as $userChannel)
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="channels[]" value="{{ $userChannel->id }}"
                                               @if(in_array($userChannel->id, old('oldChannels', []))) checked="checked" @endif
                                        >
                                        {{ $userChannel->channel->name }}
                                    </label>
                                </div>
                            @endforeach

                            <button class="btn btn-success">Add Playlist</button>
                            {{ csrf_field() }}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
