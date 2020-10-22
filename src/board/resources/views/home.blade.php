@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @foreach ($data->questions as $question)
            <div class="card mb-4">
                <div class="card-header">{{ $question->title }}</div>
                <div class="card-body">
                    <div class="card-date text-right small">{{ $question->created_at->format('Y/m/d') }}</div>
                    <div class="card-text mb-3">
                        {{ $question->content }}
                    </div>
                    <div class="card-more-detail text-center"><a class="btn btn-primary stretched-link" href="{{ route('question.show', $question->id) }}">この質問の回答を見る</a></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
