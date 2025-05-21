<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Profile;
use App\Models\User;
use App\Models\Item;
use App\Models\SoldItem;
use App\Http\Requests\ProfileRequest;

class UserController extends Controller
{
    public function profile()
    {
        $profile = Profile::where('user_id', Auth::id())->first();
        return view('profile', compact('profile'));
    }

    public function updateProfile(ProfileRequest $request)
    {
        $img = $request->file('img_url');
        $img_url = isset($img) ? Storage::disk('local')->put('public/img', $img) : '';

        $profile = Profile::where('user_id', Auth::id())->first();
        if ($profile) {
            $profile->update([
                'user_id' => Auth::id(),
                'img_url' => $img_url,
                'postcode' => $request->postcode,
                'address' => $request->address,
                'building' => $request->building
            ]);
        } else {
            Profile::create([
                'user_id' => Auth::id(),
                'img_url' => $img_url,
                'postcode' => $request->postcode,
                'address' => $request->address,
                'building' => $request->building
            ]);
        }

        User::find(Auth::id())->update(['name' => $request->name]);
        return redirect('/');
    }

    public function mypage(Request $request)
    {
        $user = User::withAvg('receivedRatings', 'rating')->find(Auth::id());

        // ✅ 常に dealCount を算出（出品者 + 購入者 両方）
        $itemsAsSeller = Item::where('user_id', $user->id)
            ->where('is_dealing', true)
            ->get();

        $itemsAsBuyer = SoldItem::where('user_id', $user->id)
            ->get()
            ->filter(fn($sold) => $sold->item && $sold->item->is_dealing)
            ->map(fn($sold) => $sold->item);

        $allDealItems = $itemsAsSeller->merge($itemsAsBuyer)->unique('id');

        $dealCount = $allDealItems->sum(fn($item) => $item->unreadMessages()->count());

        // 表示する items の切り替え
        if ($request->page === 'buy') {
            $items = SoldItem::where('user_id', $user->id)
                ->get()
                ->map(fn($sold) => $sold->item);
        } elseif ($request->page === 'deal') {
            $items = $allDealItems->map(function ($item) {
                $item->unread_count = $item->unreadMessages()->count();
                $item->latest_message_at = optional($item->tradeMessages->last())->created_at ?? $item->updated_at;
                return $item;
            })->sortByDesc('latest_message_at')->values();
        } else {
            $items = Item::where('user_id', $user->id)->get();
        }

        // 表示しているアイテムにも unread_count を追加しておく（画像側で表示用）
        foreach ($items as $item) {
            if (method_exists($item, 'unreadMessages') && !isset($item->unread_count)) {
                $item->unread_count = $item->unreadMessages()->count();
            }
        }

        return view('mypage', compact('user', 'items', 'dealCount'));
    }
}
