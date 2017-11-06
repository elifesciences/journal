eLife Journal
=============

[![Build Status](https://alfred.elifesciences.org/buildStatus/icon?job=test-journal)](https://alfred.elifesciences.org/job/test-journal/)

Journal is a stateless PHP application that reads and presents data from the [eLife Sciences API](https://api.elifesciences.org/). It is run at [elifesciences.org](https://elifesciences.org/).

Dependencies
------------

* [Composer](https://getcomposer.org/)
* [npm](https://www.npmjs.com/) and [Node.js 6](https://nodejs.org/)
* [PHP 7](https://php.net/)

Installation
------------

1. Create `app/config/parameters.yml` based on `app/config/parameters.yml.dist`.
2. Run `npm install`
3. Run `composer install`
4. Run `node_modules/.bin/gulp`

Running the tests
-----------------

`vendor/bin/phpunit`

Running the site
----------------

Running `bin/console server:start` will start the site in the `dev` environment (run `bin/console help server:start` for more details).

*Note in production [use a proper server](https://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html).*
