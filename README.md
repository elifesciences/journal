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

<details>
<summary>Windows - tips and tricks</summary>
When using Windows to bypass the main errors we recommend to follow the next :

1. Before you cloned the repo, make sure that you configure git to use the correct line endings.

    * [Explanation](https://stackoverflow.com/a/71209401) / [More detailed](https://stackoverflow.com/q/10418975)
    * Easy fix : `git config --global core.autocrlf input`

2. Make sure you use Windows Linux Subsystem (WSL) or at least git bash.

    * [Guide to use WSL](https://adamtheautomator.com/windows-subsystem-for-linux/)
    * [Guide to use Git Bash](https://www.geeksforgeeks.org/working-on-git-bash/)

3. Use the recommended versions for PHP + extensions and Composer :

    * `PHP 7.3.33-7+ubuntu22.04.1+deb.sury.org+2 (cli) (built: Sep 29 2022 22:23:16) ( NTS )`

      How to install : [Guide 1](https://5balloons.info/how-to-install-php-v-7-3-on-ubuntu-20-04) / [Guide 2](https://computingforgeeks.com/how-to-install-php-ubuntu-debian/)

    * `Composer version 2.2`

      How to install : [Step 1](https://getcomposer.org/download/)
</details>

Running the site locally
----------------------------------------------

1. Run either:
  - `make dev` to start a local server, displaying dummy data.
  - `make prod` to start a local server, displaying production data.
2. Open `http://localhost:8080` in your browser.

To stop the containers, press Ctrl+C or run `make stop` in another terminal.

To remove all cached state and artifacts from your local copy, run `make clean`.

Important : Creating `composer.lock` on local and permanent updates to composer files in general should only be done from the container, be aware that if this is done from local can generate additional errors. To avoid any errors running `composer update` in the container is safer than running it in the local dev env. ( ex: `docker-compose run composer update elife/patterns` )

When running the site locally via Docker, the parameters are supplied by `/.docker/parameters.yaml`.

Using a feature branch of the `patterns-php` library
----------------------------------------------------

In order to test visible changes that come from `patterns-php` when running `journal` locally, run:

`make update-patterns BRANCH=[branch-name-from-patterns-php]` 

Running fast tests
-----------------

To run all tests:

```
make test
```

To run a test file:

```
make test OPTIONS="--filter BrowseControllerTest"
```

To run a single test:

```
make test OPTIONS="--filter BrowseControllerTest::it_shows_reviewed_preprints_on_results"
```

To see the list of tests that have been run:
```
make test OPTIONS="--filter BrowseControllerTest --testdox"
```

Running slow feature tests
-------------

To run all feature tests:

```
make feature-test
```

To run only one feature test:

```
make feature-test FEATURE=features/podcast-episode.feature
```

Linting PHP code
----------------

To check for any linting issues, run:

```
make lint
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
