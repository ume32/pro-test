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
        'is_dealing', // ✅ 取引中フラグ
    ];

    /** 
     * 出品者ユーザー 
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 商品コンディション 
     */
    public function condition()
    {
        return $this->belongsTo(Condition::class);
    }

    /**
     * いいね（1対多）
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function liked()
    {
        return Like::where('item_id', $this->id)
            ->where('user_id', Auth::id())
            ->exists();
    }

    public function likeCount()
    {
        return Like::where('item_id', $this->id)->count();
    }

    /**
     * コメント（1対多）
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function getComments()
    {
        return $this->comments()->get();
    }

    /**
     * カテゴリー（中間テーブル経由）
     */
    public function categoryItem()
    {
        return $this->hasMany(CategoryItem::class);
    }

    public function categories()
    {
        return $this->categoryItem->map(function ($item) {
            return $item->category;
        });
    }

    /**
     * 自分の出品かどうか
     */
    public function mine()
    {
        return $this->user_id == Auth::id();
    }

    /**
     * 売却済みかどうか
     */
    public function sold()
    {
        return $this->soldItem()->exists();
    }

    /**
     * sold_items テーブルとのリレーション（1対1）
     */
    public function soldItem()
    {
        return $this->hasOne(SoldItem::class, 'item_id');
    }

    /**
     * 取引メッセージ（1対多）
     */
    public function tradeMessages()
    {
        return $this->hasMany(TradeMessage::class);
    }

    /**
     * 未読メッセージ（現在のユーザー以外の未読）
     */
    public function unreadMessages()
    {
        return $this->tradeMessages()
            ->where('user_id', '!=', Auth::id())
            ->whereNull('read_at');
    }

    /**
     * 検索用スコープ
     */
    public function scopeItem($query, $item_name)
    {
        return $query->where('name', 'like', '%' . $item_name . '%');
    }
}
