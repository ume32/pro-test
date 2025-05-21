<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TradeMessageRequest extends FormRequest
{
    public function authorize()
    {
        return true; // ログインユーザーのみ許可
    }

    public function rules()
    {
        return [
            'message' => 'required|max:400',
            'image' => 'nullable|image|mimes:jpeg,png,jpg', // ← jpg 追加 & image バリデーション
        ];
    }

    public function messages()
    {
        return [
            'message.required' => '本文を入力してください',
            'message.max' => '本文は400文字以内で入力してください',
            'image.image' => '画像ファイルを選択してください',
            'image.mimes' => '「.png」または「.jpeg」形式でアップロードしてください',
        ];
    }
}
