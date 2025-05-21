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

// ▼ 認証が必要なページ（ミドルウェア：web, auth, verified）
Route::middleware(['web', 'auth', 'verified'])->group(function () {

    // ▼ 出品・いいね・コメント
    Route::get('/sell', [ItemController::class, 'sellView']);
    Route::post('/sell', [ItemController::class, 'sellCreate']);
    Route::post('/item/like/{item_id}', [LikeController::class, 'create']);
    Route::post('/item/unlike/{item_id}', [LikeController::class, 'destroy']);
    Route::post('/item/comment/{item_id}', [CommentController::class, 'create']);

    // ▼ 購入フロー
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'index'])
        ->middleware('purchase')->name('purchase.index');
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'purchase'])
        ->middleware('purchase');
    Route::get('/purchase/{item_id}/success', [PurchaseController::class, 'success'])->name('purchase.success');
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'address']);
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress']);

    // ▼ マイページ
    Route::get('/mypage', [UserController::class, 'mypage']);
    Route::get('/mypage/profile', [UserController::class, 'profile']);
    Route::post('/mypage/profile', [UserController::class, 'updateProfile']);

    // ▼ 取引チャット・メッセージ・完了・評価
    Route::get('/trade/{item_id}', [TradeMessageController::class, 'show'])->name('trade.show');
    Route::post('/trade/{item_id}', [TradeMessageController::class, 'store'])->name('trade.store');
    Route::post('/trade/{item_id}/complete', [TradeController::class, 'complete'])->name('trade.complete');
});

// ▼ Fortify認証（ログイン・登録）
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('email');
Route::post('/register', [RegisteredUserController::class, 'store']);

// ▼ メール認証画面（初回表示）
Route::get('/email/verify', fn() => view('auth.verify-email'))
    ->middleware('auth')
    ->name('verification.notice');

// ▼ メール認証通知送信
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証リンクを送信しました。');
})->middleware('auth')->name('verification.send');

// ▼ メール認証処理
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/mypage/profile');
})->middleware(['auth', 'signed'])->name('verification.verify');
