@extends('layouts.default')

@section('title','マイページ')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/index.css') }}">
<link rel="stylesheet" href="{{ asset('/css/mypage.css') }}">
@endsection

@section('content')
@include('components.header')

<div class="container">
    <div class="user">
        <div class="user__info">
            <div class="user__img">
                @if (isset($user->profile->img_url))
                    <img class="user__icon" src="{{ asset('img/' . basename($user->profile->img_url)) }}" alt="">
                @else
                    <img class="user__icon" src="{{ asset('img/icon.png') }}" alt="">
                @endif
            </div>
            <div>
                <p class="user__name">{{ $user->name }}</p>
                @if ($user->rating)
                    <p class="user__rating">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="star">{{ $i <= round($user->rating) ? '★' : '☆' }}</span>
                        @endfor
                    </p>
                @endif
            </div>
        </div>
        <div class="mypage__user--btn">
            <a class="btn2" href="/mypage/profile">プロフィールを編集</a>
        </div>
    </div>

    <div class="border">
        <ul class="border__list">
            <li class="{{ request('page') === 'sell' ? 'active' : '' }}">
                <a href="/mypage?page=sell" class="{{ request('page') === 'sell' ? 'selected' : '' }}">出品した商品</a>
            </li>
            <li class="{{ request('page') === 'buy' ? 'active' : '' }}">
                <a href="/mypage?page=buy" class="{{ request('page') === 'buy' ? 'selected' : '' }}">購入した商品</a>
            </li>
            <li class="{{ request('page') === 'deal' ? 'active' : '' }}">
                <a href="/mypage?page=deal" class="{{ request('page') === 'deal' ? 'selected' : '' }}">
                    取引中の商品
                    @if ($dealCount > 0)
                        <span class="badge">{{ $dealCount }}</span>
                    @endif
                </a>
            </li>
        </ul>
    </div>

    <div class="items">
        @foreach ($items->sortByDesc('latest_message_at') as $item)  {{-- ★ FN004: 最新メッセージ順にソート --}}
            <div class="item">
                @if (request('page') === 'deal')
                    <a href="{{ route('trade.show', ['item_id' => $item->id]) }}">
                @else
                    <a href="/item/{{ $item->id }}">
                @endif
                        <div class="item__img--container">
                            {{-- ★ 画像 --}}
                            <img src="{{ asset('img/' . basename($item->img_url)) }}" class="item__img" alt="商品画像">

                            @if (isset($item->unread_count) && $item->unread_count > 0)
                                <span class="notify-top">{{ $item->unread_count }}</span>
                            @endif
                        </div>
                        <p class="item__name">{{ $item->name }}</p>
                    </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
