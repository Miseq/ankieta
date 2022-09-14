FROM php:8.1.3-fpm
RUN  apt-get update
RUN  apt-get install -y --no-install-recommends libfreetype6-dev libjpeg62-turbo-dev libpng-dev 
RUN  docker-php-ext-configure gd  --with-jpeg=/usr/include/ --with-freetype=/usr/include/
RUN  docker-php-ext-install -j$(nproc) gd
RUN  apt-get update
RUN  docker-php-ext-install bcmath
RUN  apt-get install git -y
RUN  apt-get install mariadb-client -y
RUN  set -eux; 
RUN  apt install zlib1g-dev && apt-get install zlib1g;
RUN  apt-get install cmake libfreetype6-dev libfontconfig1-dev xclip -y;
RUN   apt update;
RUN 	 apt-get install \
		coreutils \
		libjpeg-dev \
		libpng-dev \
		libzip-dev \
	-y ; 

RUN apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql


RUN 	docker-php-ext-configure gd \
 		--enable-gd \
		--with-freetype \
		--with-jpeg=/usr/include \
	; 

RUN 	docker-php-ext-install \
		opcache \
		pdo_pgsql \
		zip; 

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/

RUN  apt install nginx -y
    COPY ./docker/nginx/conf.d /etc/nginx/conf.d


RUN  apt install wget \
		gnupg \
		lsb-release -y
RUN  mkdir -p /var/www/html/web/sites/default/files 
RUN 	chmod -R 765 /var/www/html/ 
RUN 	chown -R www-data:www-data /var/www/html 
RUN 	chmod -R 777 /var/www/html/web/sites/default 
RUN 	cd /var/www/html/web/sites/default 
# RUN 	cp ./default.settings.php /var/www/html/web/sites/default/settings.php 
# RUN 	chmod -R 777 /var/www/html/web/sites/default/settings.php



# RUN  ls;
#RUN  chown -R www-data /var/www/html/web/sites/default



COPY . /var/www/html
