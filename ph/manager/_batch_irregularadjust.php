<?php
include_once "../class/clsystem.php";


$targetym = isset($_GET['targetym']) ? $_GET['targetym'] : "";

if($targetym == ""){
    echo "パラメータ不正";
    exit;
}

$cl = new CLSYSTEM();

$cl->targetym = $targetym;

#202204で繰越の人
$original_pids = array(
'272',

);

#202204で繰越の人
/*
$original_pids = array(
'155',
'12',
'220',
'222',
'224',
'225',
'219',
'230',
'231',
'183',
'218',
);
*/
#202205のどの対象か？
/*
$original_pids = array(
    '220',
'222',
'180',
'182',
'215',
'214',
'223',
'221',
'241',
'200',
'197',
'198',
'199',
'237',
'253',
'201',
'202',
'254',
'246',
'204',
'98',
'206',
'209',
'239',
);
*/
/*
$original_pids = array(
    '183',
    '218',
    '219',
);
*/
/*
$original_pids = array(
'223',
'15',
'17',
'48',
'121',
'91',
'130',
'194',
'224',
'225',
'219',
'226',
'228',
'229',
'231',
'98',
);
*/

$data = $cl->irregularAdjust($original_pids);




?>