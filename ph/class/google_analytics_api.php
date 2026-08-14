<?php
// ライブラリ読み込み
require_once ('../libs/google-api-php-client/src/Google_Client.php');
// Analytics なのでこれを
require_once ('../libs/google-api-php-client/src/contrib/Google_AnalyticsService.php');

	class GOOGLE_ANALYTICS_API {
		
		//google service account
		#var $client_id  = '297591827451.apps.googleusercontent.com'; //APIsコンソール ServiceアカウントのClient ID
		#var $service_account_name = '297591827451@developer.gserviceaccount.com'; //APIsコンソールのEmail address
		#var $key_file = '../libs/google-api-php-client/key/2bfde8c87eee238acccd0241ccc60561f46e2525-privatekey.p12'; //Serviceアカウント作成時にダウンロードした秘密鍵
		var $client_id  = '491711598700-27luvrrlnljce9kto75pimu089l07d80.apps.googleusercontent.com'; //APIsコンソール ServiceアカウントのClient ID
		var $service_account_name = '491711598700-27luvrrlnljce9kto75pimu089l07d80@developer.gserviceaccount.com'; //APIsコンソールのEmail address
		var $key_file = '../libs/templates/EPARK hairsalon-76f20b1d0eba.p12'; //Serviceアカウント作成時にダウンロードした秘密鍵
		
		// 項目
		var $salon_id = null;
		#var $profile_id = '78535817'; //取得したいプロファイルID
		var $profile_id = '97087464';
		var $analytics;//Googleのライブラリクラスを指定
		var $start_date;
		var $ebd_date;
		
		var $division_date = 'dayly';
		
		//結果保存用
		var $referrer_list = array();
		var $keyword_list = array();
		
		function __construct() {
			//デフォルトの実行日付を指定(2日前～1日前のデータ取得)
			$this->start_date = date('Y-m-d',strtotime('-2 days'));
			$this->end_date = date('Y-m-d',strtotime('-1 days'));
			
			/*---- サービスアカウントでの認証部分 -----*/
			// Set your client id, service account name, and the path to your private key.
			// For more information about obtaining these keys, visit:
			// https://developers.google.com/console/help/#service_accounts
			// Make sure you keep your key.p12 file in a secure location, and isn't
			// readable by others.
			// Load the key in PKCS 12 format (you need to download this from the
			// Google API Console when the service account was created.
			
			$client = new Google_Client();
			$key = file_get_contents($this->key_file);
			$client->setClientId($this->client_id);
			
			$client->setAssertionCredentials(
				new Google_AssertionCredentials(
					$this->service_account_name,
					array('https://www.googleapis.com/auth/analytics'), 
					$key
				)
			);
			/*---- /サービスアカウントでの認証部分 -----*/
			
			$this->analytics = new Google_AnalyticsService($client);
		}

		function runMain($mode=null) {
			try {
		
				// Get the user's first view (profile) ID.
				if (isset($this->profile_id)) {
		
					// Query the Core Reporting API.
					if($mode){
						$results = $this->getResultsExtend($mode);
					}else{
						$results = $this->getResults();
					}
		
					// Output the results.
					return $results;
				}
		
			} catch (apiServiceException $e) {
				// Error from the API.
				//print 'There was an API error : ' . $e->getCode() . ' : ' . $e->getMessage();
		
			} catch (Exception $e) {
				//print 'There wan a general error : ' . $e->getMessage();
			}
		}
		
		function getResults() {
			$dimension_list = array(
					'ga:pageTitle',
					'ga:pagePath',
			);
			
			//metricsとdimentionsを設定
			$optParams = array(
					'dimensions'  => implode(',',$dimension_list),
					'sort'        => '-ga:pageviews',
					'max-results' => '100',
			);
			
			//検索パラメータ
			if($this->salon_id){
				$optParams['filters'] = 'ga:pagePath=~.*'.$this->salon_id.'.*';
			}
		
			//取得データ設定
			//GA標準画面に基づいてパラメータを取得
			$metrics_list = array(
					'ga:pageviews',
					'ga:uniquePageviews',
					'ga:avgTimeOnPage',
					'ga:visits',
					'ga:visitBounceRate',
					'ga:exitRate',
//					'ga:percentNewVisits',
//					'ga:newVisits',
//					'ga:visitors',
//					'ga:organicSearches',
//					'ga:pageviewsPerVisit',
			);
			
			$result = $this->analytics->data_ga->get(
					'ga:' . $this->profile_id, //ids 必須
					$this->start_date,       //start-date 必須
					$this->end_date,       //end-date 必須
					implode(',',$metrics_list), //metrics  必須
					$optParams
			);
			
			//返却データの整形
			$replace_array = array_merge($dimension_list,$metrics_list);
			foreach($result['totalsForAllResults'] as $key => $value){
				$key_name = preg_replace('/^ga\:/','',$key);
				//時刻データだけ整形
				if($key_name == 'avgTimeOnPage'){
					$value = gmdate('H:i:s', $value);
				}
				
				$fixed_result['total'][$key_name] = $value;
			}
			
			foreach($result['rows'] as $key => $value){
				foreach($value as $child_key => $child_value){
					$key_name = preg_replace('/^ga\:/','',$replace_array[$child_key]);
					
					//時刻データだけ整形
					if($key_name == 'avgTimeOnPage'){
						$child_value = gmdate('H:i:s', $child_value);
					}
					
					$fixed_result['result'][$key][$key_name] = $child_value;
				}
			}
			
			return $fixed_result;
		}
		
		function getResultsExtend($mode=null) {
			if($mode == 'desktop' || $mode == 'mobile' || $mode == 'report'){
				$dimension_list = array(
						$this->getDivisionDate(),
						'ga:deviceCategory',
						'ga:pagePath',
						'ga:fullReferrer',
						'ga:keyword',
				);
			}else{
				$dimension_list = array(
						$this->getDivisionDate(),
						'ga:deviceCategory',
				);
			}
			
			if($mode == 'report'){
				$dimension_list[] = 'ga:operatingSystem';
			}
			
			//metricsとdimentionsを設定
			$optParams = array(
					'dimensions'  => implode(',',$dimension_list),
					'sort'        => $this->getDivisionDate(),
					'max-results' => '100000',
// 					'segment' => 'gaid::-11',
			);
				
			//検索パラメータ
			if($this->salon_id){
				$optParams['filters'] = 'ga:pagePath=~.*'.$this->salon_id.'.*';
			}
		
			//取得データ設定
			//GA標準画面に基づいてパラメータを取得
			$metrics_list = array(
					'ga:pageviews',
					'ga:uniquePageviews',
			);
				
				
				/*
				$prms = array(
				'ga:' . $this->profile_id, //ids 必須
					$this->start_date,       //start-date 必須
					$this->end_date,       //end-date 必須
					implode(',',$metrics_list), //metrics  必須
					$optParams
				);
				
				print_r($prms);
				*/
				
			$result = $this->analytics->data_ga->get(
					'ga:' . $this->profile_id, //ids 必須
					$this->start_date,       //start-date 必須
					$this->end_date,       //end-date 必須
					implode(',',$metrics_list), //metrics  必須
					$optParams
			);
			
			#print_r($result);
				
			//返却データの整形
			$replace_array = array_merge($dimension_list,$metrics_list);
			foreach($result['totalsForAllResults'] as $key => $value){
				$key_name = preg_replace('/^ga\:/','',$key);
				//時刻データだけ整形
				if($key_name == 'avgTimeOnPage'){
					$value = gmdate('H:i:s', $value);
				}
		
				$fixed_result['total'][$key_name] = $value;
			}
			
			#$page_list = array('top','photo','menu1','menu2','menu3','menu4','access','coupon','print','contents','total');
			$page_list = array('top','menu','staff','staff_detail','hairstyle','hairstyle_detail','access','total');
			if($this->start_date && $this->end_date){
				
				if($this->division_date == 'dayly'){
					$base_date = date('Y-m-d',strtotime($this->start_date));
					$week_day = date('Y/m/d', strtotime($base_date)).'('.$this->getWeekDay($base_date).')';
				}
				elseif($this->division_date == 'monthly'){
					$base_date = date('Y-m',strtotime($this->start_date));
					$week_day = $base_date;
				}
				
				while(strtotime($base_date) <= strtotime($this->end_date)){
					$date_list[$base_date] = array();
					$date_list[$base_date]['display_date'] = $week_day;
					$date_list[$base_date]['weekday'] = $this->getWeekDay($base_date);
					$date_list[$base_date]['desktop'] = array();
					$date_list[$base_date]['mobile'] = array();
					if($mode == 'desktop' || $mode == 'mobile'){
						foreach($page_list as $page_name){
							$date_list[$base_date]['desktop'][$page_name] = array();
						}
						foreach($page_list as $page_name){
							$date_list[$base_date]['mobile'][$page_name] = array();
						}
					}
					
					if($this->division_date == 'dayly'){
						$base_date = date('Y-m-d',strtotime($base_date) + strtotime('+1 day',0));
						$week_day = date('Y/m/d', strtotime($base_date)).'('.$this->getWeekDay($base_date).')';
					}
					elseif($this->division_date == 'monthly'){
						$base_date = date('Y-m',strtotime($base_date) + strtotime('+1 month',0));
						$week_day = $base_date;
					}
				}
			}
			
			if(isset($result['rows'])){
				foreach($result['rows'] as $key => $value){
					if($this->division_date == 'dayly'){
						$target_date = date('Y-m-d',strtotime($value[0]));
					}elseif ($this->division_date == 'monthly'){
						$target_date = date('Y-m',strtotime($value[0].'01'));
					}
					//レポート出力の場合は出力種別を変更
					if($mode == 'report'){
						if($value[1] == 'mobile' ){
							if($value[5] == 'iOS' || $value[5] == 'Android' || $value[5] == 'BlackBerry' ){
								$type = 'smartphone';
							}else{
								$type = 'mobile';
							}
						}else{
							$type = 'desktop';
						}
					}else{
						if($value[1] == 'mobile'){
							$type = 'mobile';
						}
						else{
							$type = 'desktop';
						}
					}
					
					
					$path_name = '';
					if($mode == 'desktop' || $mode == 'mobile' || $mode == 'report'){
						if(preg_match('/^\/sp/',$value[2])){
							$value[2] = str_replace('/sp','',$value[2]);
						}
						
						$path_list = explode('/',$value[2]);
						//TOP
						if(!isset($path_list[2]) || !$path_list[2]){
							$path_name = 'top';
						}
						elseif(preg_match('/menu/',$path_list[2])){
							$path_name = 'menu';
						}
						elseif(preg_match('/photo/',$path_list[2])){
							$path_name = 'photo';
						}
						elseif(preg_match('/access/',$path_list[2])){
							$path_name = 'access';
						}
						elseif(preg_match('/staff/',$path_list[2])){
							$path_name = 'staff';
						}
						elseif(preg_match('/staff_detail/',$path_list[2])){
							$path_name = 'staff_detail';
						}
						elseif(preg_match('/hairstyle/',$path_list[2])){
							$path_name = 'hairstyle';
						}
						elseif(preg_match('/hairstyle_detail/',$path_list[2])){
							$path_name = 'hairstyle_detail';
						/*}
						elseif(preg_match('/print/',$path_list[2])){
							$path_name = 'print';
						*/
						}else{
							$path_name = 'etc';
						}
					}
					
					foreach($value as $child_key => $child_value){
						$key_name = preg_replace('/^ga\:/','',$replace_array[$child_key]);
							
						//時刻データだけ整形
						if($key_name == 'date'){
							continue;
						}
						
						if($mode == 'desktop' || $mode == 'mobile' || $mode == 'report'){
							if($key_name == 'pageviews'){
								if(!isset($date_list[$target_date][$type][$path_name][$key_name])){
									$date_list[$target_date][$type][$path_name][$key_name] = 0;
								}
								$date_list[$target_date][$type][$path_name][$key_name] += $child_value;
// 								if($target_date == '2014-02'){
// 									if($path_name == 'top' && $type == 'desktop'){
// 										d($target_date);
// 										d($type);
// 										d($path_name);
// 										d($key_name);
// 										d($child_value);
										
// 										d($date_list[$target_date][$type][$path_name][$key_name]);
// 									}
// 								}
							}
							elseif($key_name == 'fullReferrer'){
								if($child_value == 'google'){
									$child_value = 'google.co.jp';
								}
								if(!isset($this->referrer_list[$type][$child_value])){
									$this->referrer_list[$type][$child_value] = 0;
								}
								$this->referrer_list[$type][$child_value]++;
							}
							elseif($key_name == 'keyword'){
								if($child_value == '(not set)'){
									continue;
								}
								if(!isset($this->keyword_list[$type][$child_value])){
									$this->keyword_list[$type][$child_value] = 0;
								}
								$this->keyword_list[$type][$child_value]++;
							}
						}else{
							if($key_name == 'pageviews'){
								if(!isset($date_list[$target_date][$type][$key_name])){
									$date_list[$target_date][$type][$key_name] = 0;
								}
								$date_list[$target_date][$type][$key_name] += $child_value;
							}
						}
					}
				}
			}
			
			if($mode == 'desktop' || $mode == 'mobile' || $mode == 'report'){
				foreach($date_list as $target_date => $v1){
					foreach($v1 as $type => $v2){
						if ($v2 && is_array($v2)){
							foreach($v2 as $path_name => $v3){
								if($path_name == 'total'){
									continue;
								}
								
								if(!isset($date_list[$target_date][$type]['total']['pageviews'])){
									$date_list[$target_date][$type]['total']['pageviews'] = 0;
								}
								if (isset($date_list[$target_date][$type][$path_name]['pageviews'])){
									$date_list[$target_date][$type]['total']['pageviews'] += $date_list[$target_date][$type][$path_name]['pageviews'];
								}
							}
						}
					}
				}
			}
			
			return $date_list;
		}
		
		function getWeekDay($date){
			$week = array("日", "月", "火", "水", "木", "金", "土");
			$time = strtotime($date);
			$w = date("w", $time);
			return $week[$w];
		}
		
		function getDivisionDate(){
			if($this->division_date == 'monthly'){
				$return = 'ga:yearMonth';
			}elseif($this->division_date == 'dayly'){
				$return = 'ga:date';
			}else{
				$return = 'ga:date';
			}
			
			return $return;
		}
		
		function getReferrerList(){
			if(isset($this->referrer_list['desktop'])){
				arsort($this->referrer_list['desktop']);
			}
			if(isset($this->referrer_list['mobile'])){
				arsort($this->referrer_list['mobile']);
			}
			return $this->referrer_list; 
		}
		
		function getKeywordList(){
			if(isset($this->keyword_list['desktop'])){
				arsort($this->keyword_list['desktop']);
			}
			if(isset($this->keyword_list['mobile'])){
				arsort($this->keyword_list['mobile']);
			}
			return $this->keyword_list;
		}
		
		function getCallEvent() {
			$dimension_list = array(
					'ga:eventLabel'
			);
			
			//metricsとdimentionsを設定
			$optParams = array(
					'dimensions'  => implode(',',$dimension_list),
					'sort'        => '-ga:eventValue',
					'max-results' => '100',
			);
			
			//検索パラメータ
			if($this->salon_id){
				$optParams['filters'] = 'ga:eventLabel=~.*'.$this->salon_id.'.*;ga:eventAction==Call';
			}
		
			//取得データ設定
			//GA標準画面に基づいてパラメータを取得
			$metrics_list = array(
					#'ga:pageviews',
					'ga:eventValue',
			);
			#echo "start:".$this->start_date." end;".$this->end_date;
			$result = $this->analytics->data_ga->get(
					'ga:' . $this->profile_id, //ids 必須
					$this->start_date,       //start-date 必須
					$this->end_date,       //end-date 必須
					implode(',',$metrics_list), //metrics  必須
					$optParams
			);
			
			//返却データの整形
			$replace_array = array_merge($dimension_list,$metrics_list);
			foreach($result['totalsForAllResults'] as $key => $value){
				$key_name = preg_replace('/^ga\:/','',$key);
				//時刻データだけ整形
				if($key_name == 'avgTimeOnPage'){
					$value = gmdate('H:i:s', $value);
				}
				
				$fixed_result['total'][$key_name] = $value;
			}
			
			foreach($result['rows'] as $key => $value){
				foreach($value as $child_key => $child_value){
					$key_name = preg_replace('/^ga\:/','',$replace_array[$child_key]);
					
					//時刻データだけ整形
					if($key_name == 'avgTimeOnPage'){
						$child_value = gmdate('H:i:s', $child_value);
					}
					
					$fixed_result['result'][$key][$key_name] = $child_value;
				}
			}
			
			return $fixed_result;
		}
	}
