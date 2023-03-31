ARG node_version
ARG composer_dev_arg
ARG php_version
ARG image_tag=latest

# NPM

FROM node:${node_version} as npm

RUN apt-get update && apt-get install --no-install-recommends -y \
    nasm \
    libvips-dev \
    && rm -rf /var/lib/apt/lists/*

COPY npm-shrinkwrap.json \
    package.json \
    ./

RUN npm install

# Composer

FROM composer:1.6.4 as composer

RUN apk add --no-cache \
    patch

COPY composer.json \
    composer.lock \
    ./

RUN composer --no-interaction install ${composer_dev_arg} --ignore-platform-reqs --no-autoloader --no-suggest --prefer-dist

COPY test/ test/
COPY src/ src/

RUN composer --no-interaction dump-autoload ${composer_dev_arg} --classmap-authoritative

# Assets-builder

FROM npm AS assets

RUN apt-get update && apt-get install --no-install-recommends -y \
    libvips \
    && rm -rf /var/lib/apt/lists/*

COPY --from=npm /node_modules/ node_modules/

COPY assets/images/ assets/images/
COPY gulpfile.js ./
COPY --from=composer /app/vendor/elife/patterns/resources/assets/ vendor/elife/patterns/resources/assets/

RUN node_modules/.bin/gulp assets

# Dockerfile

FROM elifesciences/php_7.1_fpm:${php_version} as app

ENV PROJECT_FOLDER=/srv/journal/
ENV PHP_ENTRYPOINT=web/app.php
WORKDIR ${PROJECT_FOLDER}

USER root
RUN pecl install redis && \
    docker-php-ext-enable redis && \
    rm -rf /tmp/pear/
RUN mkdir -p build var && \
    chown --recursive elife:elife . && \
    chown --recursive www-data:www-data var

COPY --chown=elife:elife .docker/smoke_tests.sh ./
COPY --chown=elife:elife bin/ bin/
COPY --chown=elife:elife web/ web/
COPY --chown=elife:elife app/ app/
COPY --chown=elife:elife build/critical-css/ build/critical-css/
COPY --from=assets --chown=elife:elife /build/rev-manifest.json build/
COPY --from=assets --chown=elife:elife /web/ /srv/journal/web/
COPY --from=composer --chown=elife:elife /app/vendor/ vendor/
COPY --chown=elife:elife src/ src/

USER www-data

HEALTHCHECK --interval=5s CMD HTTP_HOST=localhost assert_fpm /ping 'pong'

FROM app as app_tests
COPY --chown=elife:elife test/ test/

# Critical_css

FROM pinterb/jq:0.0.16 as jq
FROM npm as critical_css

# From list at https://developers.google.com/web/tools/puppeteer/troubleshooting#chrome_headless_doesnt_launch
RUN apt-get update && apt-get install --no-install-recommends -y \
    fonts-liberation \
    gconf-service \
    libappindicator1 \
    libasound2 \
    libatk1.0-0 \
    libcups2 \
    libdbus-1-3 \
    libgconf-2-4 \
    libgtk-3-0 \
    libnspr4 \
    libnss3 \
    libvips \
    libx11-xcb1 \
    libxcomposite1 \
    libxcursor1 \
    libxdamage1 \
    libxfixes3 \
    libxi6 \
    libxrandr2 \
    libxss1 \
    libxtst6 \
    locales \
    lsb-release \
    unzip \
    xdg-utils \
    && rm -rf /var/lib/apt/lists/*

COPY --from=jq /usr/local/bin/jq /usr/bin/jq

RUN mkdir -p build/critical-css

COPY --from=npm /node_modules/ node_modules/

COPY check_critical_css.sh \
    critical-css.json \
    gulpfile.js \
    ./

CMD node_modules/.bin/gulp critical-css:generate && ./check_critical_css.sh

# CI

FROM app as ci

USER root
RUN mkdir -p build/ci var/fixtures && \
    chown --recursive www-data:www-data build/ci var/fixtures

COPY --from=jq /usr/local/bin/jq /usr/bin/jq

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY --chown=elife:elife \
    behat.yml.dist \
    phpunit.xml.dist \
    phpcs.xml.dist \
    ./
COPY --chown=elife:elife .ci/ .ci/
COPY --chown=elife:elife assets/tests/ assets/tests/
COPY --chown=elife:elife composer.json composer.lock ./
COPY --from=composer --chown=elife:elife /app/vendor/ vendor/
COPY --chown=elife:elife features/ features/
COPY --chown=elife:elife test/ test/

USER www-data

# Web

FROM nginx:1.13.12-alpine as web

COPY .docker/nginx-default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /srv/journal/web/ /srv/journal/web/
