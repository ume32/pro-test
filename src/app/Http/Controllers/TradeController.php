<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\TradeRating;

class TradeController extends Controller
{
    public function complete(Request $request, $item_id)
    {
        $item = Item::findOrFail($item_id);

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        // 既に評価済みかどうかを確認
        $alreadyRated = TradeRating::where('item_id', $item->id)
            ->where('rater_id', Auth::id())
            ->exists();

        if (!$alreadyRated) {
            // 評価対象のユーザー（出品者 or 購入者）
            $rateeId = ($item->user_id === Auth::id())
                ? optional($item->soldItem)->user_id // 出品者が評価する＝購入者を対象
                : $item->user_id; // 購入者が評価する＝出品者を対象

            // 評価登録
            TradeRating::create([
                'item_id'  => $item->id,
                'rater_id' => Auth::id(),
                'ratee_id' => $rateeId,
                'rating'   => $request->rating,
            ]);
        }

        // ✅ 評価送信後に商品一覧画面にリダイレクト（FN014対応）
        return redirect()->route('items.list')->with('message', '評価を送信しました。');
    }
}
