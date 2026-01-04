<?php
	class COMMONCONST {
		
		
		var $m_category = array(
			'A' => array('kigo'=>'A', 'title'=>'初・再診料', 'tensu'=>0),
			'B' => array('kigo'=>'B', 'title'=>'入院科等', 'tensu'=>0),
			'C' => array('kigo'=>'C', 'title'=>'医学管理等', 'tensu'=>0),
			'D' => array('kigo'=>'D', 'title'=>'在宅医療', 'tensu'=>0),
			'E' => array('kigo'=>'E', 'title'=>'検査', 'tensu'=>0),
			'F' => array('kigo'=>'F', 'title'=>'画像診断', 'tensu'=>0),
			'G' => array('kigo'=>'G', 'title'=>'投薬', 'tensu'=>0),
			'H' => array('kigo'=>'H', 'title'=>'注射', 'tensu'=>0),
			'I' => array('kigo'=>'I', 'title'=>'リハビリテーション', 'tensu'=>0),
			'J' => array('kigo'=>'J', 'title'=>'処置', 'tensu'=>0),
			'K' => array('kigo'=>'K', 'title'=>'手術', 'tensu'=>0),
			'L' => array('kigo'=>'L', 'title'=>'麻酔', 'tensu'=>0),
			'M' => array('kigo'=>'M', 'title'=>'病理診断', 'tensu'=>0),
			'N' => array('kigo'=>'N', 'title'=>'放射線治療/精神科専門療法', 'tensu'=>0),
			'O' => array('kigo'=>'O', 'title'=>'その他', 'tensu'=>0),
			// 'A' => array('kigo'=>'A', 'title'=>'初・再診料', 'tensu'=>0),
			// 'B' => array('kigo'=>'B', 'title'=>'医学管理等', 'tensu'=>0),
			// 'C' => array('kigo'=>'C', 'title'=>'在宅医療', 'tensu'=>0),
			// 'D' => array('kigo'=>'D', 'title'=>'検査', 'tensu'=>0),
			// 'E' => array('kigo'=>'E', 'title'=>'画像診断', 'tensu'=>0),
			// 'F' => array('kigo'=>'F', 'title'=>'投薬', 'tensu'=>0),
			// 'G' => array('kigo'=>'G', 'title'=>'注射', 'tensu'=>0),
			// 'H' => array('kigo'=>'H', 'title'=>'リハビリテーション', 'tensu'=>0),
			// 'I' => array('kigo'=>'I', 'title'=>'処置', 'tensu'=>0),
			// 'J' => array('kigo'=>'J', 'title'=>'手術', 'tensu'=>0),
			// 'K' => array('kigo'=>'K', 'title'=>'麻酔', 'tensu'=>0),
			// 'L' => array('kigo'=>'L', 'title'=>'放射線治療', 'tensu'=>0),
			// 'M' => array('kigo'=>'M', 'title'=>'歯冠修復及び欠損補綴', 'tensu'=>0),
			// 'N' => array('kigo'=>'N', 'title'=>'歯科矯正', 'tensu'=>0),
			// 'O' => array('kigo'=>'O', 'title'=>'病理診断', 'tensu'=>0),

			#未定義カテゴリを暫定で用意
			'P' => array('kigo'=>'P', 'title'=>'不明１', 'tensu'=>0),
			'Q' => array('kigo'=>'Q', 'title'=>'不明２', 'tensu'=>0),
			'R' => array('kigo'=>'R', 'title'=>'不明３', 'tensu'=>0),
			'S' => array('kigo'=>'S', 'title'=>'不明４', 'tensu'=>0),
			'T' => array('kigo'=>'T', 'title'=>'不明５', 'tensu'=>0),
			'U' => array('kigo'=>'U', 'title'=>'不明６', 'tensu'=>0),
			'V' => array('kigo'=>'V', 'title'=>'不明７', 'tensu'=>0),
			'W' => array('kigo'=>'W', 'title'=>'不明８', 'tensu'=>0),
			'X' => array('kigo'=>'X', 'title'=>'不明９', 'tensu'=>0),
			'Y' => array('kigo'=>'Y', 'title'=>'不明１０', 'tensu'=>0),
			'Z' => array('kigo'=>'Z', 'title'=>'不明１１', 'tensu'=>0),

			'-' => array('kigo'=>'-', 'title'=>'その他', 'tensu'=>0),

		);


		var $m_bank_classification = array("","普通","当座");






		// 曜日
		var $m_week = array(
			1 => '月曜日','火曜日','水曜日','木曜日','金曜日','土曜日','日曜日','祝日'
		);
				
		
		
		// 都道府県
		var $m_prefecture = array( 0 => '選択して下さい',
							1 => '北海道',
							2 => '青森県',
							3 => '岩手県',
							4 => '宮城県',
							5 => '秋田県',
							6 => '山形県',
							7 => '福島県',
							8 => '茨城県',
							9 => '栃木県',
							10 => '群馬県',
							11 => '埼玉県',
							12 => '千葉県',
							13 => '東京都',
							14 => '神奈川県',
							15 => '新潟県',
							16 => '富山県',
							17 => '石川県',
							18 => '福井県',
							19 => '山梨県',
							20 => '長野県',
							21 => '岐阜県',
							22 => '静岡県',
							23 => '愛知県',
							24 => '三重県',
							25 => '滋賀県',
							26 => '京都府',
							27 => '大阪府',
							28 => '兵庫県',
							29 => '奈良県',
							30 => '和歌山県',
							31 => '鳥取県',
							32 => '島根県',
							33 => '岡山県',
							34 => '広島県',
							35 => '山口県',
							36 => '徳島県',
							37 => '香川県',
							38 => '愛媛県',
							39 => '高知県',
							40 => '福岡県',
							41 => '佐賀県',
							42 => '長崎県',
							43 => '熊本県',
							44 => '大分県',
							45 => '宮崎県',
							46 => '鹿児島県',
							47 => '沖縄県');
		

		
    
	}
?>