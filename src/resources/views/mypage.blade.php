@extends('layouts.default')

<!-- タイトル -->
@section('title','マイページ')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/index.css') }}">
<link rel="stylesheet" href="{{ asset('/css/mypage.css') }}">
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')

<div class="container">
    <div class="user">
        <div class="user__info">
            <div class="user__img">
                @if (isset($user->profile->img_url))
                    <img class="user__icon" src="{{ asset('img/' . basename($user->profile->img_url)) }}" alt="">
                @else
                    <img id="myImage" class="user__icon" src="{{ asset('img/icon.png') }}" alt="">
                @endif
            </div>
            <p class="user__name">{{ $user->name }}</p>
            @if ($user->rating)
                <p class="user__rating">
                    @for ($i = 1; $i <= 5; $i++)
                        <span class="star">{{ $i <= round($user->rating) ? '★' : '☆' }}</span>
                    @endfor
                </p>
            @endif
        </div>
        <div class="mypage__user--btn">
            <a class="btn2" href="/mypage/profile">プロフィールを編集</a>
        </div>
    </div>

    <div class="border">
        <ul class="border__list">
            <li class="{{ request('page') === 'sell' ? 'active' : '' }}">
                <a href="/mypage?page=sell">出品した商品</a>
            </li>
            <li class="{{ request('page') === 'buy' ? 'active' : '' }}">
                <a href="/mypage?page=buy">購入した商品</a>
            </li>
            <li class="{{ request('page') === 'deal' ? 'active' : '' }}">
                <a href="/mypage?page=deal">
                    取引中の商品
                    @if ($dealCount > 0)
                        <span class="badge">{{ $dealCount }}</span>
                    @endif
                </a>
            </li>
        </ul>
    </div>

    <div class="items">
        @foreach ($items as $item)
        <div class="item">
            <a href="/item/{{ $item->id }}">
                <div class="item__img--container{{ $item->sold() ? ' sold' : '' }}">
                    <img src="{{ asset('img/' . basename($item->img_url)) }}" class="item__img" alt="商品画像">
                    @if (isset($item->unread_count) && $item->unread_count > 0)
                        <span class="notify">{{ $item->unread_count }}</span>
                    @endif
                </div>
                <p class="item__name">{{ $item->name }}</p>
            </a>
        </div>
        @endforeach
    </div>
</div>

@endsection
