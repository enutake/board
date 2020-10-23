@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">{{ $data->question->title }}</div>
                <div class="card-body">
                    <div class="card-date text-right small">{{ $data->question->created_at->format('Y/m/d') }}</div>
                    <div class="card-text mb-3">
                        {{ $data->question->content }}
                    </div>
                    <div class="card-user text-right small">{{ $data->question->users->name }}さん</div>
                </div>
            </div>
            <div class="text-center mb-4"><a href="" class="answer-question-btn btn btn-primary text-center">この質問に回答する</a></div>
            @foreach($data->answers as $answer)
            <div class="card mb-4">
                <div class="card-body">
                    <div class="card-date text-right small">{{ $answer->created_at->format('Y/m/d') }}</div>
                    <div class="card-text mb-3">
                        {{ $answer->content }}
                    </div>
                    <div class="card-user text-right small">{{ $answer->users->name }}さん</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
