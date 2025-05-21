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
        if (isset($img)) {
            $img_url = Storage::disk('local')->put('public/img', $img);
        } else {
            $img_url = '';
        }

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

        User::find(Auth::id())->update([
            'name' => $request->name
        ]);

        return redirect('/');
    }

    public function mypage(Request $request)
    {
        $user = User::find(Auth::id());
        $dealCount = 0; // 初期化

        if ($request->page === 'buy') {
            $items = SoldItem::where('user_id', $user->id)->get()->map(function ($sold_item) {
                return $sold_item->item;
            });
        } elseif ($request->page === 'deal') {
            // 取引中の商品を取得（例: is_dealing フラグが true の場合）
            $items = Item::where('user_id', $user->id)
                ->where('is_dealing', true)
                ->get()
                ->map(function ($item) {
                    // unread_count が必要な場合の処理（例：unreadMessages() 関数）
                    $item->unread_count = $item->unreadMessages()->count(); // モデルにリレーションが必要
                    return $item;
                });

            // 未読メッセージ数の合計
            $dealCount = $items->sum('unread_count');
        } else {
            $items = Item::where('user_id', $user->id)->get();
        }

        return view('mypage', compact('user', 'items', 'dealCount'));
    }
}
