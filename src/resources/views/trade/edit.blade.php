@extends('layouts.default')

@section('title', 'メッセージ編集')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/trade.css') }}">
@endsection

@section('content')
@include('components.header')

<div class="trade-content edit-container">
    <h2 class="edit-title">メッセージを編集</h2>

    @if ($errors->any())
        <div class="error-messages">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('trade.update', ['message' => $message->id]) }}" class="edit-form">
        @csrf
        @method('PATCH')

        <div>
            <label for="message">メッセージ内容</label>
            <textarea id="message" name="message">{{ old('message', $message->message) }}</textarea>
        </div>

        <div class="edit-form-actions">
            <button type="submit" class="btn-submit">更新する</button>
            <a href="{{ route('trade.show', ['item_id' => $message->item_id]) }}" class="btn-cancel">キャンセル</a>
        </div>
    </form>
</div>
@endsection
