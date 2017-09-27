@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Edit playlists for {{ $userChannel->channel->name }}
                    </div>

                    <div class="panel-body">
                        @if($errors && $errors->any())
                            @foreach($errors->all() as $error)
                                <div class="alert alert-danger" role="alert">{{ $error }}</div>
                            @endforeach
                        @endif

                        <form method="post" action="{{ route('userChannels.update', ['userChannel' => $userChannel]) }}">
                            @foreach ($playlists as $playlist)
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="playlists[]" value="{{ $playlist->id }}"
                                               @if(in_array($playlist->id, old('playlists', $userChannel->playlists()->pluck('id')->toArray()))) checked="checked" @endif
                                        >
                                        {{ $playlist->name }}
                                    </label>
                                </div>
                            @endforeach

                            <button class="btn btn-success">Edit Channel</button>
                            {{ csrf_field() }}
                            {{ method_field('PUT') }}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
