# board
## URL
https://boardboard.tokyo

## 概要
Laravelで作成したシンプルなQ&Aアプリ

## ローカル開発環境セットアップ

### 必要な環境
- Docker
- Docker Compose

### セットアップ手順

1. リポジトリをクローン
```bash
git clone https://github.com/enutake/board.git
cd board
```

2. 環境変数ファイルを作成
```bash
cp .env.example .env
```

3. Dockerコンテナを起動
```bash
docker-compose up -d
```

4. 依存関係をインストール
```bash
docker-compose exec web composer install
```

5. アプリケーションキーを生成
```bash
docker-compose exec web php artisan key:generate
```

6. データベースマイグレーション
```bash
docker-compose exec web php artisan migrate
```

### アクセス情報
- **アプリケーション**: http://localhost:8080
- **HTTPS**: https://localhost:8443
- **データベース**: localhost:3307（ユーザー: root, パスワード: 環境変数で設定）

### よく使うコマンド
```bash
# コンテナの起動
docker-compose up -d

# コンテナの停止
docker-compose down

# ログの確認
docker-compose logs

# Webコンテナのシェルに入る
docker-compose exec web bash
```

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
