@extends('layouts.default')

@section('title', $item->name . 'の取引')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/trade.css') }}">
@endsection

@section('content')
@include('components.header')

<div class="trade-container">
    <div class="sidebar">
        <p class="sidebar-title">その他の取引</p>
        @foreach ($dealingItems as $sideItem)
            <a href="{{ route('trade.show', ['item_id' => $sideItem->id]) }}"
               class="sidebar-item {{ $item->id === $sideItem->id ? 'active' : '' }}">
                {{ $sideItem->name }}
            </a>
        @endforeach
    </div>

    <div class="trade-content">
        <div class="trade-header">
            <div class="trade-user">
                <img class="icon" src="{{ asset('img/icon.png') }}" alt="ユーザー">
                <p><strong>{{ $item->user->name }}</strong> さんとの取引画面</p>
            </div>
            {{-- モーダル表示トリガー --}}
            <button type="button" class="btn-complete" onclick="openModal()">取引を完了する</button>
        </div>

        <div class="trade-item">
            <img src="{{ asset('img/' . basename($item->img_url)) }}" class="item-image" alt="商品画像">
            <div>
                <h2>{{ $item->name }}</h2>
                <p>{{ number_format($item->price) }}円</p>

                @if ($averageRating)
                    <p class="average-rating">平均評価：
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="star {{ $i <= round($averageRating) ? 'filled' : '' }}">★</span>
                        @endfor
                        ({{ number_format($averageRating, 1) }})
                    </p>
                @else
                    <p class="average-rating">評価はまだありません。</p>
                @endif
            </div>
        </div>

        <div class="trade-messages">
            @foreach ($messages as $message)
                <div class="message {{ $message->user_id === auth()->id() ? 'my-message' : 'other-message' }}">
                    <p class="user-name">{{ $message->user->name }}</p>
                    <div class="message-body">
                        @if ($message->message)
                            <p>{{ $message->message }}</p>
                        @endif
                        @if ($message->image_path)
                            <img src="{{ Storage::url($message->image_path) }}" class="message-image" alt="画像">
                        @endif
                    </div>
                    @if ($message->user_id === auth()->id())
                        <div class="actions">
                            <a href="#">編集</a>
                            <a href="#">削除</a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('trade.store', ['item_id' => $item->id]) }}" enctype="multipart/form-data" class="trade-form">
            @csrf
            <input type="text" name="message" placeholder="取引メッセージを記入してください" value="{{ old('message') }}">
            <input type="file" name="image">
            <button type="submit" class="send-button">
                <img src="{{ asset('img/e99395e98ea663a8400f40e836a71b8c4e773b01.jpg') }}" alt="送信" class="send-icon">
            </button>
        </form>
    </div>
</div>

{{-- ✅ モーダル --}}
<div class="modal-overlay hidden" id="ratingModal">
    <div class="modal">
        <h2>取引が完了しました。</h2>
        <p>今回の取引相手はどうでしたか？</p>
        <form method="POST" action="{{ route('trade.complete', ['item_id' => $item->id]) }}" onsubmit="return closeModalOnSubmit()">
            @csrf
            <div class="stars">
                @for ($i = 1; $i <= 5; $i++)
                    <label>
                        <input type="radio" name="rating" value="{{ $i }}" required>
                        <span class="star">★</span>
                    </label>
                @endfor
            </div>
            <button class="btn-submit" type="submit">送信する</button>
        </form>
    </div>
</div>

{{-- ✅ JS処理 --}}
<script>
    function openModal() {
        const modal = document.getElementById('ratingModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    function closeModalOnSubmit() {
        // モーダルを即時非表示（見た目だけでも閉じたいとき）
        const modal = document.getElementById('ratingModal');
        if (modal) {
            modal.classList.add('hidden');
        }
        return true; // フォーム送信はそのまま実行
    }

    @if (session('show_complete_modal'))
        document.addEventListener('DOMContentLoaded', function () {
            openModal();
        });
    @endif
</script>
@endsection
