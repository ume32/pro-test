<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>取引完了通知</title>
</head>
<body>
    <p>{{ $buyer->name }}さんが、あなたの商品「{{ $item->name }}」の取引を完了しました。</p>

    <p>価格：{{ number_format($item->price) }}円</p>

    <p>COACHTECHフリマにアクセスして、評価や確認をお願いします。</p>

    <p><a href="{{ url('/trade/' . $item->id) }}">取引画面へ移動</a></p>

    <br>
    <p>※このメールはシステムから自動送信されています。</p>
</body>
</html>
