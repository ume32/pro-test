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
            @if (!session('rating_submitted'))
                <button type="button" class="btn-complete" onclick="openModal()">取引を完了する</button>
            @endif
        </div>

        <div class="trade-item">
            <img src="{{ asset('img/' . basename($item->img_url)) }}" class="item-image" alt="商品画像">
            <div>
                <h2 class="item-name">{{ $item->name }}</h2>
                <p class="item-price">{{ number_format($item->price) }}円</p>
            </div>
        </div>

        <div class="trade-messages" id="tradeMessages">
            @foreach ($messages as $message)
                <div class="message {{ $message->user_id === auth()->id() ? 'my-message' : 'other-message' }}">
                    <div class="message-container">
                        <img class="message-icon" src="{{ asset('img/icon.png') }}" alt="アイコン">
                        <div>
                            <p class="user-name">{{ $message->user->name }}</p>
                            <div class="message-body">
                                @if ($message->message)
                                    <p>{{ $message->message }}</p>
                                @endif
                                @if ($message->image_path)
                                    <img src="{{ asset($message->image_path) }}" class="message-image" alt="画像">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- エラー表示 --}}
        @if ($errors->any())
            <div class="error-messages">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- メッセージ投稿フォーム --}}
        <form method="POST" action="{{ route('trade.store', ['item_id' => $item->id]) }}" enctype="multipart/form-data" class="trade-form">
            @csrf
            <input type="text" name="message" placeholder="取引メッセージを記入してください" value="">

            <label class="file-label">
                <span>画像を追加</span>
                <input type="file" name="image" id="imageInput">
            </label>

            <span id="file-name" class="file-name"></span>
            <img id="preview" class="preview-image" style="display:none;" alt="画像プレビュー">

            <button type="submit" class="send-button-outside">
                <img src="{{ asset('img/e99395e98ea663a8400f40e836a71b8c4e773b01.jpg') }}" alt="送信" class="send-icon">
            </button>
        </form>
    </div>
</div>

{{-- モーダル --}}
<div class="modal-overlay hidden" id="ratingModal">
    <div class="modal">
        <h2>取引が完了しました。</h2>
        <p>今回の取引相手はどうでしたか？</p>
        <form method="POST" action="{{ route('trade.complete', ['item_id' => $item->id]) }}">
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

<script>
    function openModal() {
        document.getElementById('ratingModal')?.classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', () => {
        // 星評価のスクリプト
        const radios = document.querySelectorAll('.stars input[type="radio"]');
        radios.forEach((radio, index) => {
            radio.addEventListener('change', () => {
                document.querySelectorAll('.stars .star').forEach((star, i) => {
                    star.classList.toggle('filled', i < index + 1);
                });
            });
        });

        // ファイルプレビュー
        const input = document.getElementById('imageInput');
        const fileName = document.getElementById('file-name');
        const preview = document.getElementById('preview');

        input.addEventListener('change', function () {
            if (input.files.length > 0) {
                fileName.textContent = input.files[0].name;
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                fileName.textContent = '';
                preview.style.display = 'none';
            }
        });

        // ✅ 自動スクロール：一番下へ
        const messageContainer = document.getElementById('tradeMessages');
        if (messageContainer) {
            messageContainer.scrollTop = messageContainer.scrollHeight;
        }
    });
</script>
@endsection
