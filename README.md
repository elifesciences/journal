eLife Journal
=============

Dependencies
------------

* [Composer](https://getcomposer.org/)
* [Puli CLI](http://puli.io)
* PHP 7

Installation
-------------

1. `composer install`
2. `puli publish --install`
3. Create `app/config/parameters.yml` from `app/config/parameters.yml.dist`

Running the tests
-----------------

`vendor/bin/phpunit`

Running the site
----------------

`bin/console server:start [--env=prod]`

*Note in production [use a proper server](https://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html).*
