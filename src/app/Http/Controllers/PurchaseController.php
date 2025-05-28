<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddressRequest;
use App\Models\Item;
use App\Models\User;
use App\Models\SoldItem;
use App\Models\Profile;
use Stripe\StripeClient;

class PurchaseController extends Controller
{
    public function index($item_id, Request $request)
    {
        $item = Item::findOrFail($item_id);
        $user = User::find(Auth::id());
        return view('purchase', compact('item', 'user'));
    }

    public function purchase($item_id, Request $request)
    {
        $item = Item::findOrFail($item_id);
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));

        $user_id = Auth::id();
        $amount = $item->price;
        $sending_postcode = $request->destination_postcode;
        $sending_address = urlencode($request->destination_address);
        $sending_building = urlencode($request->destination_building ?? '');

        $checkout_session = $stripe->checkout->sessions->create([
            'payment_method_types' => [$request->payment_method],
            'payment_method_options' => [
                'konbini' => ['expires_after_days' => 7],
            ],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => ['name' => $item->name],
                    'unit_amount' => $item->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('purchase.success', [
                'item_id' => $item_id,
                'user_id' => $user_id,
                'amount' => $amount,
                'sending_postcode' => $sending_postcode,
                'sending_address' => $sending_address,
                'sending_building' => $sending_building,
            ]),
        ]);

        return redirect($checkout_session->url);
    }

    public function success($item_id, Request $request)
    {
        if (!$request->user_id || !$request->amount || !$request->sending_postcode || !$request->sending_address) {
            throw new Exception("Missing query parameters");
        }

        $item = Item::findOrFail($item_id);
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));

        $stripe->charges->create([
            'amount' => $request->amount,
            'currency' => 'jpy',
            'source' => 'tok_visa',
        ]);

        SoldItem::create([
            'user_id' => $request->user_id,
            'item_id' => $item_id,
            'sending_postcode' => $request->sending_postcode,
            'sending_address' => $request->sending_address,
            'sending_building' => $request->sending_building ?? null,
        ]);

        $item->is_dealing = true;
        $item->save();

        return redirect('/')->with('flashSuccess', '決済が完了しました！');
    }

    public function address($item_id, Request $request)
    {
        $user = User::find(Auth::id());
        return view('address', compact('user', 'item_id'));
    }

    public function updateAddress(AddressRequest $request)
    {
        $user = User::find(Auth::id());

        Profile::where('user_id', $user->id)->update([
            'postcode' => $request->postcode,
            'address' => $request->address,
            'building' => $request->building
        ]);

        return redirect()->route('purchase.index', ['item_id' => $request->item_id]);
    }
}
