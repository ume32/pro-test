<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Requests\EmailVerificationRequest;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

// コントローラー
use App\Http\Controllers\UserController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\TradeMessageController;
use App\Http\Controllers\TradeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ▼ 公開ページ
Route::get('/', [ItemController::class, 'index'])->name('items.list');
Route::get('/item/{item}', [ItemController::class, 'detail'])->name('item.detail');
Route::get('/item', [ItemController::class, 'search']);

// ▼ 認証が必要なページ
Route::middleware(['auth', 'verified'])->group(function () {

    // ▼ 出品・いいね・コメント
    Route::get('/sell', [ItemController::class, 'sellView']);
    Route::post('/sell', [ItemController::class, 'sellCreate']);
    Route::post('/item/like/{item_id}', [LikeController::class, 'create']);
    Route::post('/item/unlike/{item_id}', [LikeController::class, 'destroy']);
    Route::post('/item/comment/{item_id}', [CommentController::class, 'create']);

    // ▼ 購入フロー
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'index'])->middleware('purchase')->name('purchase.index');
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'purchase'])->middleware('purchase');
    Route::get('/purchase/{item_id}/success', [PurchaseController::class, 'success']);
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'address']);
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress']);

    // ▼ マイページ
    Route::get('/mypage', [UserController::class, 'mypage']);
    Route::get('/mypage/profile', [UserController::class, 'profile']);
    Route::post('/mypage/profile', [UserController::class, 'updateProfile']);

    // ▼ 取引チャット
    Route::get('/trade/{item_id}', [TradeMessageController::class, 'show'])->name('trade.show');
    Route::post('/trade/{item_id}', [TradeMessageController::class, 'store'])->name('trade.store');
    Route::post('/trade/{item_id}/complete', [TradeController::class, 'complete'])->name('trade.complete');
});

// ▼ 認証関連
Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('email');
Route::post('/register', [RegisteredUserController::class, 'store']);

// ▼ メール認証
Route::get('/email/verify', fn() => view('auth.verify-email'))->name('verification.notice');

Route::post('/email/verification-notification', function (Request $request) {
    session()->get('unauthenticated_user')->sendEmailVerificationNotification();
    session()->put('resent', true);
    return back()->with('message', 'Verification link sent!');
})->name('verification.send');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    session()->forget('unauthenticated_user');
    return redirect('/mypage/profile');
})->name('verification.verify');
Route::get('/purchase/{item_id}/success', [PurchaseController::class, 'success'])->name('purchase.success');
