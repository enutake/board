@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('質問タイトル') }}</div>
                <div class="card-body">
                    <div class="card-date">2020/10/23</div>
                    <div class="card-content">
                        {{ __('質問内容') }}
                    </div>
                    <div class="card-more-detail">詳細を見る</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
