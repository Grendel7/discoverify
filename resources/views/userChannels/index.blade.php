@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        My Channels

                        @can('create', \App\Models\UserChannel::class)
                            <a href="{{ route('userChannels.create') }}" class="btn btn-success btn-xs pull-right">
                                New Channel
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
                            @foreach ($userChannels as $userChannel)
                                <tr>
                                    <td>
                                        <a href="{{ route('userChannels.show', ['userChannel' => $userChannel]) }}">
                                            {{ $userChannel->channel->name }}
                                        </a>
                                        ({{ $userChannel->playlists->count() }} playlists)
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('userChannels.edit', ['userChannel' => $userChannel]) }}"
                                           class="btn btn-primary btn-sm">
                                            Edit
                                        </a>
                                        <form method="post"
                                              action="{{ route('userChannels.destroy', ['userChannel' => $userChannel]) }}"
                                              style="display: inline;"
                                        >
                                            <button class="btn btn-danger btn-sm">Delete</button>
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if ($userChannels->count() == 0)
                                <tr>
                                    <td colspan="2">No channels yet.</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
