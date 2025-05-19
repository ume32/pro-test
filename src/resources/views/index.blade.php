@extends('layouts.default')

<!-- タイトル -->
@section('title','トップページ')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/index.css') }}">
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')
<div class="border">
    <ul class="border__list">
        <li>
            <a href="{{ route('items.list', ['tab' => 'recommend', 'search' => $search]) }}">おすすめ</a>
        </li>
        @if (!auth()->guest())
        <li>
            <a href="{{ route('items.list', ['tab' => 'mylist', 'search' => $search]) }}">マイリスト</a>
        </li>
        @endif
    </ul>
</div>

<div class="container">
    <div class="items">
        @foreach ($items as $item)
        <div class="item">
            <a href="/item/{{ $item->id }}">
                <div class="item__img--container{{ $item->sold() ? ' sold' : '' }}">
                    <img src="{{ asset('img/' . basename($item->img_url)) }}" class="item__img" alt="{{ $item->name }}">
                </div>
                <p class="item__name">{{ $item->name }}</p>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endsection
