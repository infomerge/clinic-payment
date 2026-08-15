# 薬科（ph / phdev）セットアップ記録

作成日: 2026-07-31  
クローン元: 本番 `dx` → `ph`、DEV `dxdev` → `phdev`

## 1. 環境対応

| 役割 | URL | フォルダ | DB 名 | 設定ファイル |
|------|-----|----------|--------|--------------|
| 本番 | https://ph.clinic-payment.com/ | `ph/` | `xs547384_ph` | `ph/class/config.php` |
| DEV | https://phdev.clinic-payment.com/ | `phdev/` | `xs547384_phdev` | `phdev/class/config.php` |

MySQL ユーザーは既存共有ユーザー（`DBUSER`）を参照。サーバー側で DB 作成と GRANT が別途必要。

## 2. DB 設定の単一化（薬科のみ適用）

`{ph,phdev}/class/config.php` に以下を定義し、アプリ側は定数参照する。

| 定数 | 意味 |
|------|------|
| `DBNAME` | データベース名 |
| `DBHOST` | ホスト |
| `DBUSER` | ユーザー |
| `DBPASS` | パスワード |
| `COD_PREFIX` | Robot Payment の `cod` 接頭辞（`ph-`） |
| `SERVICETITLE` | 画面タイトル |

反映先の例:

- `common/database.php` / `libs/common/database.php`
- `class/common.php` / `class/db_extension.php`
- 各 `manager/*.php` の PDO（`DBNAME` 接続）
- `class/clsystem.php` の `acc_result.cod` 生成（`COD_PREFIX`）

**接続情報を変えるときは config.php だけを編集する。**

※ `dx` / `im` 本体は従来どおり接続文字列が散在。単一化は薬科クローン時に入れた改善。

## 3. 実施済みの必須差分

| 項目 | 内容 |
|------|------|
| フォルダ複製 | `dx`→`ph`、`dxdev`→`phdev` |
| DB / タイトル | 上記 config |
| `cod` 接頭辞 | `generateRPdata` 系で `COD_PREFIX`（`ph-`）付与 |
| 共有コールバック | `dx/manager/result.php` に `ph` → `xs547384_ph` 分岐を追加 |
| 薬科単独 `result.php` | 自 DB（config 定数）のみ。共有入口ではない |
| `.gitignore` | `ph` / `phdev` の vendor 等を除外 |

## 4. サーバー側の残作業（コード外）

- [ ] MySQL に `xs547384_ph` / `xs547384_phdev` を作成
- [ ] スキーマを既存科目からインポート
- [ ] `DBUSER` に両 DB への権限付与
- [ ] Web / DNS: `ph.clinic-payment.com` → `ph/`、`phdev.clinic-payment.com` → `phdev/`
- [ ] Robot Payment キックバック URL が引き続き `dx/manager/result.php` であることを確認
- [ ] 調剤レセプト取込パーサの実装（現状は歯科パーサのまま）
- [ ] カテゴリ・PDF 文言の薬科向け調整

## 5. 受け入れ確認（決済）

- [ ] step2 後の `acc_result.cod` が `ph-` で始まる
- [ ] キックバック後、更新先が **`xs547384_ph`** である（dx/im を汚さない）
- [ ] dx / im 既存決済の回帰なし
