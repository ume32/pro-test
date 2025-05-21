<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\TradeMessage;
use App\Models\User;
use App\Models\Profile;
use Carbon\Carbon;

class TradeMessageController extends Controller
{
    /**
     * チャット画面表示
     */
    public function show($item_id)
    {
        $item = Item::with('user')->findOrFail($item_id);

        // サイドバー用に自分が関わる取引中商品を取得
        $dealingItems = Item::where(function ($query) {
            $query->where('user_id', Auth::id())
                ->orWhereHas('tradeMessages', function ($q) {
                    $q->where('user_id', Auth::id());
                });
        })->get();

        // チャット一覧（最新順）
        $messages = TradeMessage::where('item_id', $item->id)
            ->orderBy('created_at')
            ->get();

        // 未読 → 既読に更新
        TradeMessage::where('item_id', $item->id)
            ->where('user_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        return view('trade.show', compact('item', 'dealingItems', 'messages'));
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

        $image_path = null;
        if ($request->hasFile('image')) {
            $image_path = $request->file('image')->store('public/trade_images');
        }

        TradeMessage::create([
            'item_id' => $item_id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'image_path' => $image_path,
        ]);

        return redirect()->back();
    }
}
