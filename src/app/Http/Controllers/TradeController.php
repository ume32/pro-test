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

        $alreadyRated = TradeRating::where('item_id', $item->id)
            ->where('rater_id', Auth::id())
            ->exists();

        if (!$alreadyRated) {
            $rateeId = ($item->user_id === Auth::id())
                ? optional($item->soldItem)->user_id
                : $item->user_id;

            TradeRating::create([
                'item_id'  => $item->id,
                'rater_id' => Auth::id(),
                'ratee_id' => $rateeId,
                'rating'   => $request->rating,
            ]);
        }

        // ✅ 送信後はモーダルを再表示しないようリダイレクトだけ行う
        return redirect()->route('trade.show', ['item_id' => $item->id])->with('rating_submitted', true);
    }
}
