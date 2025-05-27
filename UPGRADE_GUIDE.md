# アップグレードガイド

## ローカル開発環境のバージョンアップ手順

### 🔄 変更内容

このアップデートで以下のバージョンが更新されました：

- **PHP**: 7.4 → 8.3
- **MySQL**: 5.7 → 8.0
- **軽微な依存関係の更新**

### 🚀 セットアップ手順

#### 1. 環境ファイルの設定

```bash
# .env.docker.exampleをコピーして.envを作成
cp .env.docker.example .env

# APP_KEYを生成（コンテナ起動後）
docker-compose exec web php artisan key:generate
```

#### 2. Dockerコンテナの再ビルド

```bash
# 既存のコンテナとイメージを削除
docker-compose down
docker-compose rm -f
docker rmi $(docker images -q)

# 新しいバージョンでビルド
docker-compose build --no-cache
docker-compose up -d
```

#### 3. 依存関係の更新

```bash
# Composer依存関係の更新
docker-compose exec web composer install

# Node.js依存関係の更新
docker-compose exec web npm install
docker-compose exec web npm run dev
```

#### 4. データベースの初期化

```bash
# マイグレーション実行
docker-compose exec web php artisan migrate

# シーダー実行（必要に応じて）
docker-compose exec web php artisan db:seed
```

#### 5. テストの実行

```bash
# テストスイート実行
docker-compose exec web php artisan test

# カバレッジレポート生成
docker-compose exec web php artisan test --coverage-html coverage
```

### ⚠️ 注意事項

#### MySQL 8.0の変更点

- **認証プラグイン**: `caching_sha2_password`がデフォルト
- **SQL MODE**: より厳密なSQL MODE設定
- **文字セット**: `utf8mb4`がデフォルト（絵文字対応）

#### PHP 8.3の変更点

- **型宣言**: より厳密な型チェック
- **非推奨機能**: 一部の関数が削除
- **パフォーマンス**: 大幅なパフォーマンス向上

### 🛠️ トラブルシューティング

#### MySQL接続エラー

```bash
# MySQL 8.0の認証問題の場合
docker-compose exec db mysql -uroot -p
ALTER USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY 'rootpassword';
FLUSH PRIVILEGES;
```

#### Composer依存関係エラー

```bash
# キャッシュクリア
docker-compose exec web composer clear-cache
docker-compose exec web composer dump-autoload
```

#### PHP構文エラー

```bash
# PHP構文チェック
docker-compose exec web php -l app/
```

### 📊 パフォーマンス向上

- PHP 8.3により約20-30%のパフォーマンス向上が期待されます
- MySQL 8.0により、JSON操作とクエリ最適化が改善されます
- より厳密な型チェックによるバグの早期発見

### 🔄 次のフェーズ

このローカル環境が正常に動作することを確認後、以下のフェーズに進みます：

1. **Laravel Framework アップグレード** (7 → 8 → 9 → 10 → 11)
2. **フロントエンド アップグレード** (Vue 2 → 3, Bootstrap 4 → 5)
3. **本番環境デプロイ**