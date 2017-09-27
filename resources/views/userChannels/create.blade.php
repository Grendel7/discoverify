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

                        <form method="post" action="{{ route('userChannels.store') }}">
                            <div class="form-group">
                                <label for="channel_url">YouTube Channel URL</label>
                                <input name="channel_url" id="channel_url" class="form-control" type="text" required value="{{ old('channel_url') }}">
                            </div>

                            <p>Playlists:</p>

                            @foreach ($playlists as $playlist)
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="playlists[]" value="{{ $playlist->id }}"
                                               @if(in_array($playlist->id, old('playlists', []))) checked="checked" @endif
                                        >
                                        {{ $playlist->name }}
                                    </label>
                                </div>
                            @endforeach

                            <button class="btn btn-success">Create Channel</button>
                            {{ csrf_field() }}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
