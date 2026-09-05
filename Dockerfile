ARG image_tag=latest
FROM elifesciences/journal_assets_builder:${image_tag} AS assets
FROM elifesciences/journal_composer:${image_tag} AS composer
FROM ghcr.io/elifesciences/php:8.3-fpm

ENV PROJECT_FOLDER=/srv/journal
ENV PHP_ENTRYPOINT=web/app.php
WORKDIR ${PROJECT_FOLDER}

USER root

RUN curl -fL --output /tmp/pie.phar https://github.com/php/pie/releases/latest/download/pie.phar
RUN mv /tmp/pie.phar /usr/local/bin/pie
RUN chmod +x /usr/local/bin/pie

# deb.debian.org's live mirror intermittently 404s on superseded bullseye-security package
# versions (Debian's security repo only keeps the latest version of each package, and the CDN's
# cached index can lag behind). Pin to the immutable snapshot.debian.org sources the base image
# already ships (commented out) instead, so this never depends on the live mirror's current state.
RUN sed -i \
    -e 's|^deb http://deb.debian.org|# deb http://deb.debian.org|' \
    -e 's|^# deb http://snapshot.debian.org|deb http://snapshot.debian.org|' \
    /etc/apt/sources.list \
    && echo 'Acquire::Check-Valid-Until "false";' > /etc/apt/apt.conf.d/10no--check-valid-until \
    && apt-get update \
    && apt-get install -y --no-install-recommends --no-install-suggests unzip libtool \
    && rm -rf /var/lib/apt/lists/*

RUN pie install phpredis/phpredis

RUN mkdir -p build var && \
    chown --recursive elife:elife . && \
    chown --recursive www-data:www-data var

COPY --chown=elife:elife .docker/smoke_tests.sh ./
COPY --chown=elife:elife composer.json composer.lock ./
COPY --chown=elife:elife bin/ bin/
COPY --chown=elife:elife web/ web/
COPY --chown=elife:elife build/critical-css/ build/critical-css/
COPY --from=assets --chown=elife:elife /build/rev-manifest.json build/
COPY --from=assets --chown=elife:elife /web/ /srv/journal/web/
COPY --from=composer --chown=elife:elife /app/vendor/ vendor/
COPY --chown=elife:elife src/ src/
COPY --chown=elife:elife config/ config/
COPY --chown=elife:elife templates/ templates/

USER www-data

HEALTHCHECK --interval=5s CMD HTTP_HOST=localhost assert_fpm /ping 'pong'
