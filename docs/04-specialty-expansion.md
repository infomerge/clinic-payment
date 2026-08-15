# 新科目追加チェックリスト（薬科 / 将来科目）

科目を増やすたびに全体調査しなくてよいよう、**毎回やる作業**と**科目固有で別途設計する作業**を分ける。

> 薬科（`ph` / `phdev`）のコード側セットアップは実施済み。詳細は [05-ph-setup.md](./05-ph-setup.md)。  
> 以下は将来の科目追加・薬科のサーバー側残作業用の汎用チェックリスト。

## A. インフラ・複製（毎回）

- [ ] 本番フォルダを複製（例: `dx` → `ph`）
- [ ] DEV フォルダを複製（例: `dxdev` → `phdev`）
- [ ] Web サーバ / DNS で URL を割り当て  
  - 例: https://ph.clinic-payment.com/ → `ph/`  
  - 例: https://phdev.clinic-payment.com/ → `phdev/`
- [ ] MySQL DB を作成（命名例）
  - 本番: `xs547384_phprod`（または `xs547384_ph`。既存の im 命名揺れに注意して決める）
  - DEV: `xs547384_ph`（または `xs547384_phdev`）
- [ ] スキーマを既存科目からコピー（テーブル構造のソース・オブ・トゥルースを決める）

## B. 設定差し替え（毎回・漏れやすい）

少なくとも次を新 DB / タイトルに合わせて更新する。

- [ ] `{ph,phdev}/class/config.php` の `DBNAME`, `SERVICETITLE` 等
- [ ] `{ph,phdev}/common/database.php`
- [ ] `{ph,phdev}/libs/common/database.php`
- [ ] `{ph,phdev}/class/common.php` など PDO 直書き箇所
- [ ] バッチ内の DB ユーザー固定値（例: step3 の PDO）を棚卸し

`rg "xs547384_" ph phdev` で旧 DB 名の残留を確認する。

## C. ロボットペイメント（毎回・最重要）

詳細は [02-robot-payment.md](./02-robot-payment.md)。

- [ ] `generateRPdata` で `cod` を **`ph-...`** にする
- [ ] `generateRPdataDEBUG` / irregular 調整など **全生成経路**でも同じ接頭辞にする
- [ ] **`dx/manager/result.php` に `ph` 分岐を追加**（共有キックバック）
- [ ] 分岐先 DB 名が実際の ph 本番 DB と一致していることを確認
- [ ] 同一 `AID` 継続か別店舗かを決定
- [ ] Robot Payment 管理画面のキックバック URL が引き続き dx `result.php` である前提を文書・運用で維持
- [ ] （任意）CSV 手動取込を持つなら接頭辞フィルタを `ph` に

最低限の分岐イメージ:

```php
$codPrefix = substr($cod, 0, 2);

if ($codPrefix === 'im') {
    $dbName = 'xs547384_improd';
} elseif ($codPrefix === 'ph') {
    $dbName = 'xs547384_phprod'; // 実際の命名に合わせる
} else {
    $dbName = 'xs547384_dx';
}
```

## D. 科目固有（薬科ではここが本体の見積もり）

決済クローンだけでは業務が回らない領域。

- [ ] 調剤レセプトのフォーマット仕様を入手
- [ ] 取込パーサを実装（歯科 `recept-upload-008` / 医科 `recept-upload-im` は流用不完全）
- [ ] `$m_category` / `commonconst.php` を調剤向けに見直し
- [ ] 請求・領収 PDF の文言・項目を確認
- [ ] 介護・自由診療を薬科で使うか方針決定
- [ ] 医療機関側画面の名称・権限を確認
- [ ] サンプルレセプト・マスタを `ph/docs` 等に配置

## E. DEV 方針（毎回決める）

- [ ] DEV でもロボペイキックバックを使うか
- [ ] 使う場合: DEV 用 URL / ルーティング / テスト用 `cod` 接頭辞の衝突回避
- [ ] 使わない場合: 手動で `acc_result` / status を更新する運用を書く

現状、`dxdev` / `imdev` の `result.php` に本番相当の横断振り分けはない。

## F. 受け入れ確認（決済まわり）

- [ ] ph で step2 実行後、`acc_result.cod` が `ph-` で始まる
- [ ] step3 で Robot Payment に届く `cod` も同じ
- [ ] キックバック後、**ph DB** の明細が status 4/5 になる
- [ ] 誤って **dx DB** の同 `cod` が更新されていない
- [ ] im / dx の既存決済が回帰していない（接頭辞分岐の順序・typo）

## G. 将来の科目を増やすときの接頭辞ルール

| 科目 | フォルダ例 | `cod` 接頭辞 | `result.php` |
|------|------------|--------------|--------------|
| 歯科 | `dx` | （なし＝デフォルト） | else 節 |
| 内科 | `im` | `im-` | `=== 'im'` |
| 薬科 | `ph` | `ph-` | `=== 'ph'` |
| 新規 | 2 文字略称 | `{xx}-` | 分岐追加 |

**制約:** `substr($cod, 0, 2)` 前提のため、略称は英数字 2 文字で衝突しないこと。  
歯科をデフォルトのままにする限り、未知接頭辞は歯科に落ちる危険がある。余裕があれば歯科にも `dx-` を付けてデフォルト分岐を「未知はエラー」にする改善を検討する（既存 `cod` との移行が必要）。

## H. 依頼時に渡すとよい追加情報（薬科ベンダー向け）

本 docs 一式に加え、次があると手戻りが減る。

1. 調剤レセプトのサンプルファイルと項目定義
2. クローン元は `dx` 本番であること
3. 決済は既存 Robot Payment 店舗共有・キックバックは dx `result.php` 改修が必須であること
4. DEV/本番の URL・DB 命名の確定値
5. 画面タイトル（現状 im も「DX クリニックペイメント」のまま等の既存仕様）
