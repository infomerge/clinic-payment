<!DOCTYPE_html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>Robot Payment 送信テスト</title>
</head>
    
<body>
    
    <h2 class="title_name">Robot Payment 処理項目</h2>

    <form method="post" action="payment-test-exe.php" enctype="multipart/form-data">
        <input type="radio" name="req_type" value="0">顧客番号発番<br>
        <input type="radio" name="req_type" value="1">顧客登録<br>
        <input type="radio" name="req_type" value="2">請求追加<br>
        <input type="radio" name="req_type" value="3">請求情報変更<br>
        <input type="radio" name="req_type" value="4">口座情報変更<br>
        <input type="submit" value="送信" />
    </form>
    
</body>
</html>