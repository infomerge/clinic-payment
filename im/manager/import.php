<!DOCTYPE_html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>レセプトデータの取り込み</title>
</head>
    
<body>
    
    <h2 class="title_name">レセプトデータの取り込み</h2>

    <form method="post" action="recept-upload.php" enctype="multipart/form-data">

        <br />

        <input type="file" name="upfile" size="30" />

        <br />

        <input type="submit" value="アップロード" />

    </form>

<br /><br />

    <h2 class="title_name">介護保険レセプトデータの取り込み</h2>

    <form method="post" action="kaigo-recept-upload.php" enctype="multipart/form-data">

        <br />

        <input type="file" name="upfile" size="30" />

        <br />

        <input type="submit" value="アップロード" />

    </form>
    
</body>
</html>