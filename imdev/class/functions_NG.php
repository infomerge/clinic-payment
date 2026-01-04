<?php

define("TAX", "0.08");
define("DB_PREFIX", "stg");

function getFrom($y,$m){
	$str = $y."-".$m."-21";
	return date('Y-m-d',strtotime($str));
}

function getTo($y,$m){
	$str = date('Y-m-d',strtotime($y."-".$m."-01")). " + 1month";
	$base = date('Y-m-d',strtotime($str));
	
	return date('Y-m-d',strtotime( date('Y',strtotime($base))."-".date('m',strtotime($base))."-20") );
}

function getRevenue($prm){
	#print_r($prm);
	$revenue = 0;
	switch($prm['revenue_type']){
		case 1:
			$revenue += $prm['revenue_price'] * ($prm['adult'] + $prm['child']) ;
			#foreach($prm['reserve_course'] as $val){
			#	$revenue += $prm['revenue_price'] * ($val['adult'] + $val['child']);
			#}
		break;
		case 2:
			if(isset($prm['reserve_course']) && count($prm['reserve_course'] > 0)){
				foreach($prm['reserve_course'] as $val){
					$revenue += $val['price'] * ($val['adult'] + $val['child']) * ($prm['revenue_rate'] / 100);
				}
			}
			
			#疑問：小数点は切り上げ？切り捨て？四捨五入？
		break;
	}
	return $revenue;
}

function getIwauYM($date){
	$day = date('d',strtotime($date) );
	
	if(intval($day) >= 21){
		$iwauYM = date('Y-m',strtotime($date) );
	}else{
		$str =  date('Y', strtotime($date) ) ."-" . date('m',strtotime($date) ) . "-01 - 1month";
		#echo $str."<br>";
		$iwauYM = date('Y-m', strtotime($str) );
	}
	
	return $iwauYM;
}