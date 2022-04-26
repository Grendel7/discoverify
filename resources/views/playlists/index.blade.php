@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        My Playlists

                        @can('create', \App\Models\Playlist::class)
                            <a href="{{ route('playlists.create') }}" class="btn btn-success btn-xs pull-right">
                                Add Playlist
                            </a>
                        @endcan
                    </div>

                    <div class="panel-body">
                        @if(session('success'))
                            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                        @endif

                        <table class="table table-striped">
                            <tr>
                                <th>Name</th>
                                <th class="text-right">Actions</th>
                            </tr>
                            @foreach ($playlists as $playlist)
                                <tr>
                                    <td>
                                        <a href="{{ route('playlists.show', ['playlist' => $playlist]) }}">
                                            {{ $playlist->name }}
                                        </a>
                                        ({{ $playlist->channels->count() }} channels)
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('playlists.edit', ['playlist' => $playlist]) }}"
                                           class="btn btn-primary btn-sm">
                                            Edit
                                        </a>
                                        <form method="post"
                                              action="{{ route('playlists.destroy', ['playlist' => $playlist]) }}"
                                              style="display: inline;"
                                        >
                                            <button class="btn btn-danger btn-sm">Delete</button>
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if ($playlists->count() == 0)
                                <tr>
                                    <td colspan="2">No playlists yet.</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
