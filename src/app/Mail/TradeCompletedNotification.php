<?php

namespace App\Mail;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TradeCompletedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $item;
    public $buyer;

    public function __construct(Item $item, $buyer)
    {
        $this->item = $item;
        $this->buyer = $buyer;
    }

    public function build()
    {
        return $this->subject('【COACHTECH】商品が購入されました')
            ->view('emails.trade_completed');
    }
}
