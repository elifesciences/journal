eLife Journal
=============

[![Build Status](http://ci--alfred.elifesciences.org/buildStatus/icon?job=test-journal)](http://ci--alfred.elifesciences.org/job/test-journal/)

Dependencies
------------

* [Composer](https://getcomposer.org/)
* [npm](https://www.npmjs.com/)
* PHP 7

Installation
-------------

1. Create `app/config/parameters.yml` from `app/config/parameters.yml.dist`
2. `npm install`
3. `composer install`
4. `node_modules/.bin/gulp`
5. `bin/console assets:install --symlink`

Running the site locally
------------------------
The first time running the journal, you may need to build some dependant images first.
1. Build all dependant images first
```
docker-compose build npm
docker-compose build composer
docker-compose build assets_builder
docker-compose build app
```

Then, you should be able to start and restart using these commands:

1. `docker-compose down --volumes --remove-orphans && docker-compose up --build`
2. Open `http://localhost:8080` in your browser.

### Changing configuration

When running the site locally via Docker, the parameters are supplied by `/.docker/parameters.yaml`.

To change configuration that is supplied by an environment variable, pass in the environment variable at start up. For example, to change the API URL:
`docker-compose down --volumes --remove-orphans && API_URL=https://prod--gateway.elifesciences.org docker-compose up --build`.

See `/.env` for the list of environment variables that can be passed in this way.

Regenerating critical CSS
-------------------------

`docker-compose run critical_css`

Running the tests
-----------------

`docker-compose run app vendor/bin/phpunit`

Running Behat
-------------

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

Reproduce a ci failure
----------------------

```
docker-compose -f docker-compose.yml -f docker-compose.ci.yml down -v
SELENIUM_IMAGE_SUFFIX=-debug docker-compose -f docker-compose.yml -f docker-compose.ci.yml up --build
docker-compose -f docker-compose.yml -f docker-compose.ci.yml run ci .ci/behat
```
