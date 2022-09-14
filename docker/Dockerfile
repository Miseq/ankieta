FROM php:8.1.3-fpm

RUN apt-get update
RUN apt-get install -y --no-install-recommends libfreetype6-dev libjpeg62-turbo-dev libpng-dev 

RUN docker-php-ext-configure gd  --with-jpeg=/usr/include/ --with-freetype=/usr/include/
RUN docker-php-ext-install -j$(nproc) gd

RUN apt-get update
RUN docker-php-ext-install bcmath
RUN apt-get install git -y
RUN apt-get install mariadb-client -y

RUN set -eux; 
#RUN apt-get install php-xml -y;
RUN apt install zlib1g-dev && apt-get install zlib1g;
RUN apt-get install cmake libfreetype6-dev libfontconfig1-dev xclip -y;
RUN apt update;
RUN	apt-get install \
		coreutils \
#		freetype2 \
		libjpeg-dev \
		libpng-dev \
		libzip-dev \
# postgresql-dev is needed for https://bugs.alpinelinux.org/issues/3642
	-y ; 

RUN	docker-php-ext-configure gd \
 		--enable-gd \
		--with-freetype \
		--with-jpeg=/usr/include \
	; 

RUN	docker-php-ext-install \
		opcache \
		pdo_mysql \
		zip; 

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/

RUN cd /var/www/html \
	mkdir web \
	mkdir sites \
	mkdir default \
	mkdir files \
	chmod -R 765 /var/www/html/ \
	chown -R www-data:www-data /var/www/html \
	chmod -R 777 /var/www/html/web/sites/default \
	cd /var/www/html/web/sites/default 
#	cp ./default.settings.php /var/www/html/web/sites/default/settings.php \
#	chmod -R 777 /var/www/html/web/sites/default/settings.php

# RUN ls;
# RUN chown -R www-data /var/www/html/web/sites/default
