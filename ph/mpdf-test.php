<?php
$path = __DIR__.'/vendor/autoload.php';
if(file_exists($path)){
    echo "あり";
}else{
    echo "なし";
}
require_once __DIR__.'/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML('<h1>Hello, world!</h1>');
$mpdf->Output();