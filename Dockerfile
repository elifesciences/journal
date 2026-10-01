ARG image_tag=latest
FROM elifesciences/journal_assets_builder:${image_tag} AS assets
FROM elifesciences/journal_composer:${image_tag} AS composer
FROM ghcr.io/elifesciences/php:8.5-fpm

ARG xdebug=false

ENV PROJECT_FOLDER=/srv/journal
ENV PHP_ENTRYPOINT=web/app.php
WORKDIR ${PROJECT_FOLDER}

USER root

# TODO? install gh and run gh attestation on this pie binary (look up the pie installation docs)
RUN curl -fL --output /tmp/pie.phar https://github.com/php/pie/releases/latest/download/pie.phar
RUN mv /tmp/pie.phar /usr/local/bin/pie
RUN chmod +x /usr/local/bin/pie

# deb.debian.org's live mirror intermittently 404s on superseded trixie-security package
# versions (Debian's security repo only keeps the latest version of each package, and the CDN's
# cached index can lag behind). Repoint each stanza's URIs at the immutable snapshot.debian.org
# archive the base image records above it, so this never depends on the live mirror's state.
RUN sed -i -E '$!N; s|^# (http://snapshot\.debian\.org/\S*)\nURIs: http://deb\.debian\.org/\S*|URIs: \1|; P; D' /etc/apt/sources.list.d/debian.sources \
    && echo 'Acquire::Check-Valid-Until "false";' > /etc/apt/apt.conf.d/10no--check-valid-until \
    && apt-get update \
    && apt-get install -y --no-install-recommends --no-install-suggests unzip libtool libicu-dev \
    && rm -rf /var/lib/apt/lists/*

RUN if [ "$xdebug" = "true" ]; then pie install xdebug/xdebug; fi
RUN pie install phpredis/phpredis
RUN docker-php-ext-install intl

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
