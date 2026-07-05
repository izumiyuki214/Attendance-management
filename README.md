# 勤怠管理システム

## 1. サービス概要

本サービスは、一般ユーザー（スタッフ）と管理者の双方が利用できる勤怠管理システムです。

一般ユーザーは出勤・退勤・休憩の打刻、勤怠一覧・詳細の確認、勤怠修正申請、マイ勤怠レポートの閲覧が行えます。管理者は全スタッフの日別・月別勤怠の確認、勤怠情報の直接修正、修正申請の承認、CSV形式での勤怠データ出力が行えます。

**仕様変更点**

- 勤怠一覧画面（一般・管理者共通）において、当該月（または当該日）に勤怠記録（`attendance_records`）が存在しない日付の行は表示しない仕様に変更しています。打刻が行われていない日は一覧上に表示されません。

---

## 2. 使用技術

| 用途 | 技術 |
|---|---|
| フレームワーク | Laravel 8（Framework 8.83.29） |
| 認証 | Laravel Fortify |
| バリデーション | FormRequest |
| API認証 | Laravel Sanctum |
| メール認証 | Mailhog |
| テスト | PHPUnit |
| 開発環境 | Docker（Windows + WSL2） |
| フロントエンド | Blade テンプレート、CSS（個別ファイル管理） |

---

## 3. 環境構築

### 前提

- Docker / Docker Compose がインストールされていること
- WSL2環境（Windowsの場合）

### 手順

1. リポジトリをクローン

   ```bash
   git clone https://github.com/izumiyuki214/Attendance-management.git
   cd Attendance-management
   ```

2. 環境変数ファイルを作成

   ```bash
   cp src/.env.example src/.env
   ```

   ```bash
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=laravel_db
   DB_USERNAME=laravel_user
   DB_PASSWORD=

   MAIL_FROM_ADDRESS="test@example.com"
   ```

3. Dockerコンテナを起動

   ```bash
   docker compose up -d --build
   ```

4. Composer依存関係をインストール

   ```bash
   docker compose exec php composer install
   ```

5. アプリケーションキーを生成

   ```bash
   docker compose exec php php artisan key:generate
   ```

6. マイグレーションを実行

   ```bash
   docker compose exec php php artisan migrate
   ```

7. ダミーデータを投入（Seeder）

   ```bash
   docker compose exec php php artisan db:seed
   ```

8. ブラウザでアクセス

   一般ログイン画面
   http://localhost/login

   管理者ログイン画面
   http://localhost/admin/login

### 権限関係で開けない場合
   ```bash
   sudo chmod -R 777 *
   ```

### テスト実行

   ```bash
   docker compose exec php php artisan test
   ```

---

## 4. ER図

<img src="index.drawio.png">

---

## 5. テストユーザー

Seeder実行後、以下のユーザーが作成されます。

| ユーザー | メール | パスワード | 種別 |
|---|---|---|---|
| ユーザー1 | user1@example.com | password | 一般（メール認証済み） |
| ユーザー2 | user2@example.com | password | 一般（メール認証済み） |
| ユーザー3 | user3@example.com | password | 管理者（admin_status=true） |

なお、ユーザー1にはマイ勤怠レポート機能の検証用に、過去5ヶ月分の通常勤務データと、当月17日分の通常・残業・遅刻・早退・長時間労働パターンを含む意図的なダミーデータが投入されています。

---

## 6. URL一覧

### 一般ユーザー

| 画面 | URL |
|---|---|
| 会員登録画面 | /register |
| ログイン画面 | /login |
| 打刻画面 | /attendance |
| 勤怠一覧画面 | /attendance/list |
| 勤怠詳細画面 | /attendance/detail/{id} |
| 申請一覧画面 | /stamp_correction_request/list |
| マイ勤怠レポート画面 | /attendance/report |

### 管理者

| 画面 | URL |
|---|---|
| ログイン画面 | /admin/login |
| 勤怠一覧画面 | /admin/attendance/list |
| 勤怠詳細画面 | /admin/attendance/{id} |
| スタッフ一覧画面 | /admin/staff/list |
| スタッフ別勤怠一覧画面 | /admin/attendance/staff/{id} |
| 申請一覧画面 | /stamp_correction_request/list（認証ミドルウェアで一般と区別） |
| 修正申請承認画面 | /stamp_correction_request/approve/{attendance_correct_request_id} |

