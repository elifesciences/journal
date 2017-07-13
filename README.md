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

Running the tests
-----------------

`vendor/bin/phpunit`

Running the site
----------------

`bin/console server:start [--env=prod]`

*Note in production [use a proper server](https://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html).*
