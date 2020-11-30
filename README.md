# eLife Journal

[![Build Status](http://ci--alfred.elifesciences.org/buildStatus/icon?job=test-journal)](http://ci--alfred.elifesciences.org/job/test-journal/)

## Dependencies

```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'e5325b19b381bfd88ce90a5ddb7823406b2a38cff6bb704b0acc289a09c8128d4a8ce2bbafcd1fcbdc38666422fe2806') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

Then you can run it as...

```
php composer.phar
```

- [Composer](https://getcomposer.org/)
- [npm](https://www.npmjs.com/)
- [nvm](https://github.com/nvm-sh/nvm)
- PHP 7

## Installation

```
nvm install 10.21.0
nvm use 10.21.0

```

1. Create `app/config/parameters.yml` from `app/config/parameters.yml.dist`
2. `npm install`
3. `composer install`
4. `node_modules/.bin/gulp`
5. `bin/console assets:install --symlink`

## Running the site locally

1. `docker-compose down --volumes --remove-orphans && docker-compose up --build`
2. Open `http://localhost:8080` in your browser.

### Changing configuration

When running the site locally via Docker, the parameters are supplied by `/.docker/parameters.yaml`.

To change configuration that is supplied by an environment variable, pass in the environment variable at start up. For example, to change the API URL:
`docker-compose down --volumes --remove-orphans && API_URL=https://prod--gateway.elifesciences.org docker-compose up --build`.

See `/.env` for the list of environment variables that can be passed in this way.

## Regenerating critical CSS

`docker-compose run critical_css`

## Running the tests

`docker-compose run app vendor/bin/phpunit`

## Running Behat

Behat needs the `ci` image to run, so it needs to build an additional image and use the ci configuration:

```
docker-compose -f docker-compose.yml -f docker-compose.ci.yml up --build --detach
```

To run all scenarios:

```
docker-compose -f docker-compose.yml -f docker-compose.ci.yml run ci .ci/behat
```

To run a single scenario:

```
docker-compose -f docker-compose.yml -f docker-compose.ci.yml run ci vendor/bin/behat features/article.feature
```

If you have made changes to the code and want to re-run a test then you will need to rebuild your docker containers:

```
docker-compose -f docker-compose.yml -f docker-compose.ci.yml down && docker-compose -f docker-compose.yml -f docker-compose.ci.yml up --build --detach
```

## Reproduce a ci failure

```
docker-compose -f docker-compose.yml -f docker-compose.ci.yml down -v
SELENIUM_IMAGE_SUFFIX=-debug docker-compose -f docker-compose.yml -f docker-compose.ci.yml up --build
docker-compose -f docker-compose.yml -f docker-compose.ci.yml run ci .ci/behat
```
