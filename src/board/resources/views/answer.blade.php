@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">{{ $data->question->title }}</div>
                <div class="card-body">
                    <div class="card-text mb-3">
                        {{ $data->question->content }}
                    </div>
                    <div class="card-user text-right small">{{ $data->question->users->name }}さん</div>
                </div>
            </div>
            <div class="card mb-4">
                <form method="POST" action="{{ route('answer.store') }}">
                    @csrf
                    <div class="card-header">回答する</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="textarea1">回答内容:</label>
                            <textarea id="textarea1" class="form-control"></textarea>
                        </div>
                        <div class="text-center"><button type="submit" class="btn btn-primary mb-2">回答する</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
