# board
## URL
https://boardboard.tokyo

## 概要
Laravelで作成したシンプルなQ&Aアプリ

## ローカル開発環境のセットアップ（Laravel Sail）

### 必要なソフトウェア
- Docker Desktop
- Git

### セットアップ手順

1. **リポジトリのクローン**
   ```bash
   git clone https://github.com/enutake/board.git
   cd board
   ```

2. **環境設定ファイルのコピー**
   ```bash
   cp .env.example .env
   ```

3. **Sailスクリプトの実行権限付与**
   ```bash
   chmod +x ./sail
   ```

4. **依存関係のインストール（初回のみ）**
   ```bash
   docker run --rm \
       -u "$(id -u):$(id -g)" \
       -v $(pwd):/var/www/html \
       -w /var/www/html \
       laravelsail/php80-composer:latest \
       composer install --ignore-platform-reqs
   ```

5. **コンテナの起動**
   ```bash
   ./sail up -d
   ```

6. **アプリケーションキーの生成**
   ```bash
   ./sail artisan key:generate
   ```

7. **データベースのマイグレーション**
   ```bash
   ./sail artisan migrate
   ```

8. **シーダーの実行（任意）**
   ```bash
   ./sail artisan db:seed
   ```

### 使用方法

- **アプリケーションの起動**: `./sail up`
- **アプリケーションの停止**: `./sail down`
- **Artisanコマンドの実行**: `./sail artisan [command]`
- **Composerの実行**: `./sail composer [command]`
- **NPMの実行**: `./sail npm [command]`
- **コンテナ内のBashシェル**: `./sail shell`

### アクセス
- アプリケーション: http://localhost
- データベース: localhost:3306（ユーザー: sail, パスワード: password）

### 従来のDocker設定
従来のDocker設定は `docker-compose.original.yml` として保存されています。

## 設計
下記のグーグルドライブ内に超最低限の資料が格納されています。
https://drive.google.com/drive/folders/1E0qM00mzwVwDz93Uc23M2PQgc0QoxGOr

## ページ
### トップページ
![トップページ](https://user-images.githubusercontent.com/17631154/96961682-2226f200-1540-11eb-990e-7463315ea26b.png)
### 質問ページ
![質問ページ](https://user-images.githubusercontent.com/17631154/96961779-55698100-1540-11eb-83c0-9b3d507d5984.png)
### 回答ページ
![回答ページ](https://user-images.githubusercontent.com/17631154/96961799-5e5a5280-1540-11eb-9ecd-26dad4253938.png)
![回答ページバリデーション](https://user-images.githubusercontent.com/17631154/96961803-60241600-1540-11eb-98e9-b15142744c62.png)
### 新規登録・ログインページ
![新規登録ページ](https://user-images.githubusercontent.com/17631154/96961836-716d2280-1540-11eb-9e3a-c9ca1a8c8d41.png)
![ログインページ](https://user-images.githubusercontent.com/17631154/96961845-77630380-1540-11eb-9361-73be3953e595.png)
