@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Create Video') }}</div>

                <div class="card-body">
                    <form action="{{ route('videos.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label>Title:</label>
                        <input type="text" name="title" required>
                        <label>File:</label>
                        <input type="file" name="file" required>
                        <button type="submit">Upload</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection