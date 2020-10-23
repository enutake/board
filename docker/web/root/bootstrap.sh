#!/bin/bash

: "DB用意" && {
    mysql -uroot -hdb -pmysql -e"create database board" 
}

: "migrate実行" && {
    cd /var/www/html && php artisan migrate:fresh --seed
}