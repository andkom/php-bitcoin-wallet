FROM php:7.4-cli

RUN apt-get update && apt-get install -y libdb5.3++-dev

RUN curl -O --referer https://fossies.org/linux/misc/db-18.1.40.tar.gz/ \
        https://fossies.org/linux/misc/db-18.1.40.tar.gz \
    && tar -zxf db-18.1.40.tar.gz && cd db-18.1.40/lang/php_db4/ \
    && phpize \
    && ./configure --with-db4 \
    && make \
    && make install \
    && docker-php-ext-enable db4

RUN apt-get install -y libgmp-dev \
    && docker-php-ext-install gmp