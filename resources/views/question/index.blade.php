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
            <div class="text-center mb-4"><a href="{{ route('answer.create', $data->question->id) }}" class="answer-question-btn btn btn-primary text-center">この質問に回答する</a></div>
            @foreach($data->answers as $answer)
            <div class="card mb-4">
                <div class="card-body">
                    <div class="card-date text-right small">{{ $answer->created_at->format('Y/m/d') }}</div>
                    <div class="card-text mb-3">
                        {{ $answer->content }}
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="card-user small">{{ $answer->users->name }}さん</div>
                        <div class="like-section">
                            @auth
                                <button class="btn btn-sm like-btn {{ $answer->isLikedBy(auth()->user()) ? 'btn-danger' : 'btn-outline-danger' }}" 
                                        data-answer-id="{{ $answer->id }}" 
                                        data-liked="{{ $answer->isLikedBy(auth()->user()) ? 'true' : 'false' }}">
                                    <i class="fas fa-heart"></i>
                                </button>
                            @else
                                <span class="btn btn-sm btn-outline-secondary disabled">
                                    <i class="fas fa-heart"></i>
                                </span>
                            @endauth
                            <span class="likes-count ml-1">{{ $answer->likesCount() }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const likeButtons = document.querySelectorAll('.like-btn');
    
    likeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const answerId = this.dataset.answerId;
            const isLiked = this.dataset.liked === 'true';
            const likesCountElement = this.parentElement.querySelector('.likes-count');
            
            const url = isLiked 
                ? `/answers/${answerId}/like`
                : `/answers/${answerId}/like`;
            
            const method = isLiked ? 'DELETE' : 'POST';
            
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.liked) {
                    this.classList.remove('btn-outline-danger');
                    this.classList.add('btn-danger');
                    this.dataset.liked = 'true';
                } else {
                    this.classList.remove('btn-danger');
                    this.classList.add('btn-outline-danger');
                    this.dataset.liked = 'false';
                }
                likesCountElement.textContent = data.likes_count;
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});
</script>
@endpush
