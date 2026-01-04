<?php

function convertDate($src){

	#桁数
	#5：診療月和暦GYYMM
	#6：診療月西暦YYYYMM
	#7：生年月日和暦GYYMMDD
	#8：生年月日西暦YYYYMMDD

	$strcnt = mb_strlen($src);
	$dt = "";
	
	switch($strcnt){
		case 5:
			$g = mb_substr($src, 0, 1, 'UTF-8');
			$y = mb_substr($src, 1, 2, 'UTF-8');
			$m = mb_substr($src, 3, 2, 'UTF-8');

			$y = convertG2Year($g,$y);

			$dt = $y.$m;
			break;
		case 6:
			$dt = $src;
			break;
		case 7:
			$g = mb_substr($src, 0, 1, 'UTF-8');
			$y = mb_substr($src, 1, 2, 'UTF-8');
			$m = mb_substr($src, 3, 2, 'UTF-8');
			$d = mb_substr($src, 5, 2, 'UTF-8');

			$y = convertG2Year($g,$y);

			$dt = $y.$m.$d;
			break;
		case 8:
			$dt = $src;
			break;
		default:
			break;
	}

	return $dt;
}

function convertG2Year($g,$y){
	if ($g === '5') $y += 2018;
	elseif ($g === '4') $y += 1988;
	elseif ($g === '3') $y += 1925;
	elseif ($g === '2') $y += 1911;
	elseif ($g === '1') $y += 1868;

	return $y;
}

?>
