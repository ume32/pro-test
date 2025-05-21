<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Item;
use App\Models\TradeMessage;
use App\Models\TradeRating;
use App\Models\SoldItem;
use Carbon\Carbon;

class TradeMessageController extends Controller
{
    /**
     * チャット画面表示
     */
    public function show($item_id)
    {
        $item = Item::with('user')->findOrFail($item_id);

        // ✅ 取引中の商品だけをサイドバーに表示（出品者 or 購入者 かつ sold_items にある）
        $dealingItems = Item::whereHas('soldItem', function ($query) {
            $query->where('user_id', Auth::id());
        })->orWhere(function ($query) {
            $query->where('user_id', Auth::id())
                ->whereHas('soldItem');
        })->get();

        // メッセージ一覧（時系列順）
        $messages = TradeMessage::where('item_id', $item->id)
            ->orderBy('created_at')
            ->get();

        // 自分以外の未読メッセージを既読に更新
        TradeMessage::where('item_id', $item->id)
            ->where('user_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        // ★ 評価の平均（小数点1桁まで）
        $averageRatingRaw = TradeRating::where('ratee_id', $item->user->id)->avg('rating');
        $averageRating = $averageRatingRaw ? round($averageRatingRaw, 1) : null;

        return view('trade.conversation', compact(
            'item',
            'dealingItems',
            'messages',
            'averageRating'
        ));
    }

    /**
     * メッセージ送信処理
     */
    public function store(Request $request, $item_id)
    {
        $request->validate([
            'message' => 'required|max:400',
            'image' => 'nullable|image|mimes:jpeg,png',
        ], [
            'message.required' => '本文を入力してください',
            'message.max' => '本文は400文字以内で入力してください',
            'image.image' => '画像ファイルを選択してください',
            'image.mimes' => '「.png」または「.jpeg」形式でアップロードしてください',
        ]);

        $image_path = $request->hasFile('image')
            ? $request->file('image')->store('public/trade_images')
            : null;

        TradeMessage::create([
            'item_id' => $item_id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'image_path' => $image_path,
        ]);

        return redirect()->back();
    }
}
