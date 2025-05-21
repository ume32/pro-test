<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'brand',
        'description',
        'img_url',
        'user_id',
        'condition_id',
    ];

    // ユーザーとの関連
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function condition()
    {
        return $this->belongsTo('App\Models\Condition');
    }

    public function likes()
    {
        return $this->hasMany('App\Models\Like');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function categoryItem()
    {
        return $this->hasMany('App\Models\CategoryItem');
    }

    public function categories()
    {
        return $this->categoryItem->map(function ($item) {
            return $item->category;
        });
    }

    public function liked()
    {
        return Like::where([
            'item_id' => $this->id,
            'user_id' => Auth::id()
        ])->exists();
    }

    public function likeCount()
    {
        return Like::where('item_id', $this->id)->count();
    }

    public function getComments()
    {
        return Comment::where('item_id', $this->id)->get();
    }

    public function sold()
    {
        return SoldItem::where('item_id', $this->id)->exists();
    }

    public function mine()
    {
        return $this->user_id == Auth::id();
    }

    public static function scopeItem($query, $item_name)
    {
        return $query->where('name', 'like', '%' . $item_name . '%');
    }

    // ✅ 追加：取引チャット関連
    public function tradeMessages()
    {
        return $this->hasMany(TradeMessage::class);
    }

    public function unreadMessages()
    {
        return $this->tradeMessages()
            ->where('user_id', '!=', Auth::id())
            ->whereNull('read_at'); // 既読でないメッセージのみ
    }
}
