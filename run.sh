#!/usr/bin/env bash
#-u www-data
#-u $(id -u):$(id -g)
docker run -d -v $(pwd):/var/www/html extend
docker run -e MYSQL_ALLOW_EMPTY_PASSWORD=1 -e MYSQL_DATABASE=forum -d mysql
#alias punit='php vendor/phpunit/phpunit/phpunit'
php artisan migrate --seed
#npm run watch
docker run -v "$PWD":/usr/src/app -w /usr/src/app node npm run dev
docker exec -i 1b mysql -uroot < docker/dump.sql