#!/bin/bash


service nginx start
service postgresql start

sudo -u postgres createuser root --superuser
sudo -u postgres psql -c "ALTER USER root WITH PASSWORD 'root';"
sudo -u postgres psql -c 'create database ankieta'

if test -f "/data/ankieta_dump.sql"; then
    echo "dump exists."
    pg_restore -d ankieta -1 /data/ankieta_dump.sql
fi

/var/www/html/build/./build.local.sh
sed -i "s|\$response->headers->set('X-Frame-Options'|//\$response->headers->set('X-Frame-Options'|g" /var/www/html/web/core/lib/Drupal/Core/EventSubscriber/FinishResponseSubscriber.php   

php-fpm &
cron &
