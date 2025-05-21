<?php

// src/app/Models/TradeRating.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'rater_id',
        'ratee_id',
        'rating',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function ratee()
    {
        return $this->belongsTo(User::class, 'ratee_id');
    }
}
