<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

        return view('trade.conversation', compact('item', 'dealingItems', 'messages', 'averageRating'));
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
            'item_id' => $item_id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'image_path' => $image_path,
        ]);

        return redirect()->route('trade.show', ['item_id' => $item_id]);
    }
}
