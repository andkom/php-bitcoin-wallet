FROM php:7.2-cli

RUN apt-get update && apt-get install -y libdb5.3++-dev

RUN curl -O --referer https://fossies.org/linux/misc/db-18.1.25.tar.gz/ \
        https://fossies.org/linux/misc/db-18.1.25.tar.gz \
    && tar -zxf db-18.1.25.tar.gz && cd db-18.1.25/lang/php_db4/ \
    && phpize \
    && ./configure --with-db4 \
    && make \
    && make install \
    && docker-php-ext-enable db4

RUN apt-get install libgmp-dev \
    && docker-php-ext-install gmp