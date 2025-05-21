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
        $user = User::find(Auth::id());
        $dealCount = 0;

        if ($request->page === 'buy') {
            $items = SoldItem::where('user_id', $user->id)
                ->get()
                ->map(fn($sold) => $sold->item);
        } elseif ($request->page === 'deal') {
            // 出品者としての取引中商品
            $itemsAsSeller = Item::where('user_id', $user->id)
                ->where('is_dealing', true)
                ->with('tradeMessages')
                ->get();

            // 購入者としての取引中商品
            $itemsAsBuyer = SoldItem::where('user_id', $user->id)
                ->get()
                ->filter(fn($sold) => $sold->item && $sold->item->is_dealing)
                ->map(fn($sold) => $sold->item->load('tradeMessages'));

            // マージして重複除去
            $items = $itemsAsSeller->merge($itemsAsBuyer)->unique('id')->values();

            foreach ($items as $item) {
                // 未読件数
                $item->unread_count = $item->unreadMessages()->count();
                // 最新メッセージ時刻（null対応）
                $item->latest_message_at = optional($item->tradeMessages->last())->created_at ?? $item->updated_at;
                // 合計未読数
                $dealCount += $item->unread_count;
            }

            // 最新メッセージが新しい順にソート
            $items = $items->sortByDesc('latest_message_at')->values();
        } else {
            $items = Item::where('user_id', $user->id)->get();
        }

        return view('mypage', compact('user', 'items', 'dealCount'));
    }
}
