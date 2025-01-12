@extends('layouts.app')

@section('content')
<div class="container mt-5">
        <h1>Manage Your Videos</h1>
        <a href="{{ route('videos.create') }}" class="btn btn-primary mb-3">Upload New Video</a>

        @if($videos->isEmpty())
            <p>No videos uploaded yet.</p>
        @else
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Thumbnail</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($videos as $video)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $video->title }}</td>
                            <td>
                                <img src="{{ asset('storage/thumbnails/' . $video->thumbnail) }}" alt="Thumbnail" width="100">
                            </td>
                            <td>
                                <a href="{{ route('videos.edit', $video->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('videos.destroy', $video->id) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection