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

1. `docker-compose up --build -V`
2. Open `http://localhost:8080` in your browser.

Running the tests
-----------------

`docker-compose run cli vendor/bin/phpunit`

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
docker-compose -f docker-compose.yml -f docker-compose.ci.yml run ci /bin/bash -c "JARNAIZ_JUNIT_OUTPUTDIR=build/ci/behat vendor/bin/behat features/article.feature"
```

Reproduce a ci failure
----------------------

```
SELENIUM_IMAGE_SUFFIX=-debug docker-compose -f docker-compose.yml -f docker-compose.ci.yml up --build -V
docker-compose -f docker-compose.yml -f docker-compose.ci.yml run ci .ci/behat
```
