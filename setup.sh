#!/bin/bash

echo "🚀 Laravel Sail開発環境セットアップスクリプト"
echo "============================================="

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Dockerが起動していません。Docker Desktopを起動してから再実行してください。"
    exit 1
fi

echo "📋 環境設定ファイルのコピー..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ .envファイルを作成しました"
else
    echo "⚠️ .envファイルは既に存在します"
fi

echo "🔧 Sailスクリプトに実行権限を付与..."
chmod +x ./sail

echo "📦 Composer依存関係のインストール..."
if [ ! -d "vendor" ]; then
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v $(pwd):/var/www/html \
        -w /var/www/html \
        laravelsail/php80-composer:latest \
        composer install --ignore-platform-reqs
    echo "✅ 依存関係をインストールしました"
else
    echo "⚠️ vendor ディレクトリは既に存在します"
fi

echo "🐳 Dockerコンテナの起動..."
./sail up -d

echo "🔑 アプリケーションキーの生成..."
./sail artisan key:generate

echo "🗄️ データベースのマイグレーション..."
./sail artisan migrate

echo "🌱 シーダーの実行（データベースに初期データを投入）..."
read -p "シーダーを実行しますか？ (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    ./sail artisan db:seed
    echo "✅ シーダーを実行しました"
else
    echo "⏭️ シーダーをスキップしました"
fi

echo ""
echo "🎉 セットアップ完了！"
echo ""
echo "📱 アクセス方法:"
echo "   🌐 アプリケーション: http://localhost"
echo "   🗄️ データベース: localhost:3306 (ユーザー: sail, パスワード: password)"
echo ""
echo "🛠️ よく使うコマンド:"
echo "   ./sail up          # コンテナ起動"
echo "   ./sail down        # コンテナ停止"
echo "   ./sail artisan     # Artisanコマンド"
echo "   ./sail composer    # Composerコマンド"
echo "   ./sail shell       # コンテナ内のシェル"
echo ""