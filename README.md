# Attendance-management
# 勤怠管理システム

従業員の出勤・退勤管理、休憩時間の記録、勤怠データの修正申請・承認を行うLaravelベースの勤怠管理システムです。一般ユーザーと管理者の2つのロールで異なる画面・機能を提供します。

---

## 🎯 システム概要

### 対応ユーザー
- **一般ユーザー**: 自分の勤怠情報の登録・確認・修正申請
- **管理者**: 全従業員の勤怠管理・修正申請の承認・レポート確認

### 主要技術スタック
| 用途 | 技術 |
|---|---|
| フレームワーク | Laravel |
| 認証 | Laravel Fortify / Laravel Sanctum |
| バリデーション | FormRequest |
| テスト | PHPUnit |
| 開発環境 | Docker (Windows + WSL2) |
| メール検証 | Mailhog / Mailtrap |

---


## 💻 システム要件

- **PHP**: 8.0以上
- **Laravel**: 8.0以上
- **Docker**: 最新版
- **WSL2** (Windows開発環境の場合)
- **Node.js**: 14.0以上（フロントエンド構築時）

---

## 🚀 セットアップ

### 1. リポジトリのクローン
```bash
git clone <repository-url>
cd atte
```

### 2. 環境変数の設定
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Dockerコンテナの起動
```bash
docker-compose up -d
```

### 4. 依存パッケージのインストール
```bash
composer install
npm install
```

### 5. データベース初期化
```bash
php artisan migrate
php artisan db:seed
```

### 6. メール検証の設定
Mailhog または Mailtrap を使用。`.env` の設定例：
```
MAIL_DRIVER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
```

### 7. アプリケーション起動
```bash
php artisan serve
```

アクセスURL: `http://localhost:8000`

#### テストユーザー
| メール | パスワード | 種別 |
|---|---|---|
| user1@example.com | password | 一般ユーザー |
| user2@example.com | password | 一般ユーザー |
| user3@example.com | password | 管理者 |

### クエリパラメータ（一覧取得時）
```
GET /api/v1/attendance-records?user_id=1&date=2024-01-15&month=2024-01&per_page=20&page=1
```

| パラメータ | 型 | 説明 |
|---|---|---|
| `user_id` | integer | ユーザーID（オプション） |
| `date` | date | 特定日付（形式: Y-m-d） |
| `month` | string | 月単位（形式: Y-m） |
| `per_page` | integer | ページあたりレコード数（1-100） |
| `page` | integer | ページ番号 |

### レスポンス例（GET一覧）
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "date": "2024-01-15",
      "clock_in": "09:00:00",
      "clock_out": "18:00:00",
      "status": "finished",
      "breaks": [
        {
          "break_start": "12:00:00",
          "break_end": "13:00:00"
        }
      ],
      "comment": "通常勤務"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 45
  }
}
```

### エラーレスポンス
| ステータス | レスポンス |
|---|---|
| 404 | `{ "error": "勤怠情報が見つかりませんでした。" }` |
| 422 | `{ "message": "...", "errors": { "field": ["エラーメッセージ"] } }` |
| 403 | `{ "error": "この操作を実行する権限がありません。" }` |

---

## 🧪 テスト

### テスト実行
```bash
# 全テスト実行
php artisan test

# 特定ファイルのテスト
php artisan test tests/Feature/AuthTest.php

# カバレッジレポート生成
php artisan test --coverage
```

## 📊 データベース設計

### テーブル一覧
<img src="index.drawio.png">