<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TradeMessage;

class TradeMessagePolicy
{
    public function update(User $user, TradeMessage $tradeMessage)
    {
        return $user->id === $tradeMessage->user_id;
    }

    public function delete(User $user, TradeMessage $tradeMessage)
    {
        return $user->id === $tradeMessage->user_id;
    }
}
