-- dxdev (xs547384_dxdev) 用
-- phpMyAdmin で実行してください。コードデプロイ前に実行すること。

ALTER TABLE patient_info
  ADD COLUMN patient_kana varchar(250) NOT NULL DEFAULT ''
  COMMENT '患者名カナ（ソート用・全角カタカナ）'
  AFTER patient_name;

-- 確認
SHOW COLUMNS FROM patient_info LIKE 'patient_kana';
