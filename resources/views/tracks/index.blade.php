@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        My Tracks
                    </div>

                    <div class="panel-body">
                        @if(session('success'))
                            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                        @endif

                        <table class="table table-striped">
                            <tr>
                                <th>YouTube</th>
                                <th>Spotify</th>
                                <th nowrap>Retrieved on</th>
                            </tr>
                            @foreach ($tracks as $track)
                                <tr>
                                    <td>
                                        <a href="https://www.youtube.com/watch?v={{ $track->youtube_id }}" target="_blank">
                                            {{ $track->name }}
                                        </a>
                                    </td>
                                    <td>
                                        @if ($track->spotify_id)
                                            <a href="https://open.spotify.com/track/{{ $track->spotify_id }}" target="_blank">
                                                {{ $track->spotify_name }}
                                            </a>
                                        @else
                                            {{ $track->error }}
                                        @endif
                                    </td>
                                    <td nowrap>
                                        {{ $track->created_at->format('Y-m-d') }}
                                    </td>
                                </tr>
                            @endforeach
                            @if ($tracks->count() == 0)
                                <tr>
                                    <td colspan="4">No tracks yet.</td>
                                </tr>
                            @endif
                        </table>
                    </div>

                    <div class="panel-footer text-right">
                        {{ $tracks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
