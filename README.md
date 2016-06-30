eLife Journal
=============

Dependencies
------------

* [Composer](https://getcomposer.org/)
* [Puli CLI](http://puli.io)
* PHP 7

Installation
-------------

1. Create `app/config/parameters.yml` from `app/config/parameters.yml.dist`
2. `composer install`
3. `puli publish --install`

Running the tests
-----------------

`vendor/bin/phpunit`

Running the site
----------------

`bin/console server:start [--env=prod]`

*Note in production [use a proper server](https://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html).*
