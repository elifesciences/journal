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
