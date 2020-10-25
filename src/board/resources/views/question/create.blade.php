@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="card mb-4">
                <form method="POST" action="{{ route('question.store') }}">
                    @csrf
                    <div class="card-header">質問する</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="question-content">質問内容:</label>
                            <textarea id="question-content" name="content" class="form-control"></textarea>
                        </div>
                        <div class="text-center"><button type="submit" class="btn btn-primary mb-2">質問する</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
