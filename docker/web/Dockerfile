FROM --platform=linux/amd64 php:7.4-apache

# Composerのインストール
RUN cd /usr/bin && curl -s http://getcomposer.org/installer | php && ln -s /usr/bin/composer.phar /usr/bin/composer

# 設定ファイルの配置
ADD /root/000-default.conf /etc/apache2/sites-enabled/

# ミドルウェアインストール
RUN apt-get update \
&& apt-get install -y \
git \
zip \
unzip \
vim \
libpng-dev \
libpq-dev \
default-mysql-client \
&& docker-php-ext-install pdo_mysql bcmath

# # node, npm最新化(nを利用する)
# RUN apt install -y nodejs npm \
# && npm install -g n \
# && n stable \
# && apt purge -y nodejs npm \
# && exec $SHELL -l \
# && npm -v \
# && nodejs -v

# modRewriteを有効化する
RUN mv /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled
RUN /bin/sh -c a2enmod rewrite
