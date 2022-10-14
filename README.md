eLife Journal
=============

[![Build Status](http://ci--alfred.elifesciences.org/buildStatus/icon?job=test-journal)](http://ci--alfred.elifesciences.org/job/test-journal/)

Dependencies
------------

* [Composer](https://getcomposer.org/)
* [npm](https://www.npmjs.com/)
* [PHP 7](https://www.php.net/)
* [Docker](https://www.docker.com/)
* [Docker-compose](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-docker-compose-on-ubuntu-20-04)

Docker Intallation - Running the site locally
------------------------
<!-- The first time running the journal, you may need to build some dependant images first.
1. Build all dependant images first
```
docker-compose build npm
docker-compose build composer
docker-compose build assets_builder
docker-compose build app
```
Then, you should be able to start and restart using these commands:
1. `docker-compose down --volumes --remove-orphans && docker-compose up --build` -->
1. Run `composer install` to generate vendor files locally
2. Run `docker-compose up --build`
3. Open `http://localhost:8080` in your browser.

### Changing configuration

When running the site locally via Docker, the parameters are supplied by `/.docker/parameters.yaml`.

To change configuration that is supplied by an environment variable, pass in the environment variable at start up. For example, to change the API URL:
`docker-compose down --volumes --remove-orphans && API_URL=https://prod--gateway.elifesciences.org docker-compose up --build`.

Manual Installation (may or may not work - better use docker)
-------------

1. Create `app/config/parameters.yml` from `app/config/parameters.yml.dist`
2. `npm install`
3. `composer install`
4. `node_modules/.bin/gulp`
5. `bin/console assets:install --symlink`

Regenerating critical CSS
-------------------------

`docker-compose run critical_css`

Running the tests
-----------------

To run all unit tests as in `ci`:

`docker-compose -f docker-compose.yml -f docker-compose.ci.yml run ci .ci/phpunit`

To run a single test:

```
APP_ENV=ci docker-compose run app vendor/bin/phpunit --filter AboutControllerTest
```

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

Working on Content Alerts
-------------------------

In all but the production environment the CiviCRM client is a mock. This allows the developer to demonstrate the various screens without needing to make changes to a working CiviCRM instance.

Visit http://localhost:8080/content-alerts:
- Use green@example.com to trigger existing subscription scenario
- Use any other email to trigger subscription confirmation

For user preference interface:
- Visit http://localhost:8080/content-alerts/green to prepopulate form
- Visit http://localhost:8080/content-alerts/red to trigger expired link

Expired link interface:
- Use green@example.com to trigger email sent scenario
- Use red@example.com to trigger something went wrong scenario

Unsubscribe/optout interface:
- Visit http://localhost:8080/content-alerts/unsubscribe/green to show unsubscribe form
- Visit http://localhost:8080/content-alerts/unsubscribe/red to trigger something went wrong scenario
- Visit http://localhost:8080/content-alerts/optout/green to show opt-out form
- Visit http://localhost:8080/content-alerts/optout/red to trigger something went wrong scenario

To work on the integration with CiviCRM you will have to set the environment variable `APP_ENV=prod`. And also adjust the values in `.docker/parameters.yml` for `crm_api_key`, `crm_api_site_key`, `google_api_client.refresh_token` and `google_api_client.optout_unsubscribe_spreadsheet_id` to be the same as in production.
