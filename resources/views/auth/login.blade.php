@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Login</div>
                <div class="panel-body tex-center">
                    <a href="{{ route('login.spotify') }}" class="btn btn-success btn-lg">
                        Login with Spotify
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
