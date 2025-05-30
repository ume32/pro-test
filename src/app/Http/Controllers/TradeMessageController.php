<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\TradeMessageRequest;
use App\Models\Item;
use App\Models\TradeMessage;
use App\Models\TradeRating;
use Carbon\Carbon;

class TradeMessageController extends Controller
{
    public function show($item_id)
    {
        $item = Item::with('user')->findOrFail($item_id);

        $dealingItems = Item::whereHas('soldItem', function ($query) {
            $query->where('user_id', Auth::id());
        })->orWhere(function ($query) {
            $query->where('user_id', Auth::id())->whereHas('soldItem');
        })->get();

        $messages = TradeMessage::where('item_id', $item->id)
            ->orderBy('created_at')->get();

        TradeMessage::where('item_id', $item->id)
            ->where('user_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        $averageRatingRaw = TradeRating::where('ratee_id', $item->user->id)->avg('rating');
        $averageRating = $averageRatingRaw ? round($averageRatingRaw, 1) : null;

        $alreadyRated = TradeRating::where('item_id', $item->id)
            ->where('rater_id', Auth::id())
            ->exists();

        $shouldShowRatingModal = false;

        $buyerId = optional($item->soldItem)->user_id;

        $buyerHasRated = TradeRating::where('item_id', $item->id)
            ->where('rater_id', $buyerId)
            ->exists();

        if (
            $item->user_id === Auth::id() &&
            !$alreadyRated &&
            $buyerId &&
            $buyerHasRated
        ) {
            $shouldShowRatingModal = true;
        }

        return view('trade.conversation', [
            'item' => $item,
            'dealingItems' => $dealingItems,
            'messages' => $messages,
            'averageRating' => $averageRating,
            'showRatingModal' => $shouldShowRatingModal
        ]);
    }

    public function store(TradeMessageRequest $request, $item_id)
    {
        $image_path = null;

        if ($request->hasFile('image')) {
            $filename = $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('img'), $filename);
            $image_path = 'img/' . $filename;
        }

        TradeMessage::create([
            'item_id'    => $item_id,
            'user_id'    => Auth::id(),
            'message'    => $request->message,
            'image_path' => $image_path,
        ]);

        return redirect()->route('trade.show', ['item_id' => $item_id]);
    }

    public function update(Request $request, TradeMessage $message)
    {
        $this->authorize('update', $message);

        $request->validate([
            'message' => 'required|string|max:400',
        ]);

        $message->update([
            'message' => $request->message,
        ]);

        return redirect()->route('trade.show', ['item_id' => $message->item_id])
            ->with('success', 'メッセージを更新しました。');
    }

    public function destroy(TradeMessage $message)
    {
        $this->authorize('delete', $message);

        $message->delete();

        return redirect()->route('trade.show', ['item_id' => $message->item_id])
            ->with('success', 'メッセージを削除しました。');
    }

    public function edit(TradeMessage $message)
    {
        $this->authorize('update', $message);

        return view('trade.edit', compact('message'));
    }
}
