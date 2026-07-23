# Symfony 7 Migration

## Chapter 1: Dead code removed during branch setup

These were cleaned up as part of getting the branch into a workable state.

### SpamBundleTranslatorPass

`SpamBundleTranslatorPass` was a workaround for Symfony 3.4's `IdentityTranslator` not implementing
`Symfony\Contracts\Translation\TranslatorInterface`, which `isometriks/spam-bundle` requires. This
pass nulled out the translator argument at container compile time. It must remain until the Symfony
upgrade is complete and the spam bundle picks up a real translator.

### GuzzleToSymfonyAdapter and the knpu oauth2 http_client option

`knpu/oauth2-client-bundle` v1.34.0 (installed) accepts a custom Guzzle client via `http_client`
in config. We wired it to `GuzzleToSymfonyAdapter`, which wraps a Symfony `HttpClientInterface`
to satisfy Guzzle's `ClientInterface`. The mock chain in tests was:

```
knpu bundle --> GuzzleToSymfonyAdapter (Guzzle interface)
            --> framework.http_client.clients.oauth (Symfony interface)
            --> MockApiHttpClient (saves/replays responses from files)
```

`mockApiResponse()` in `AppKernelTestCase` saves responses into `MockApiHttpClient`, including
the oauth token POST. This is how `readyToken()` in `WebTestCase` intercepts the token exchange
without hitting the real OAuth server.

A Docker environment with a newer version of knpu threw `InvalidConfigurationException:
Unrecognized option "http_client" under "knpu_oauth2_client"`, so the option, adapter, and all
related services were removed. That severed the mock chain: the knpu bundle fell back to its own
internal Guzzle client with no mock in front of it. Token requests failed silently,
`onAuthenticationFailure` fired instead of `onAuthenticationSuccess`, and the failure redirect
returned `'/'` (relative, no `ABSOLUTE_URL` flag) instead of `'http://localhost/'` -- causing
every auth test to fail with a URL mismatch.

**Restored** -- the adapter, services, and `AppKernelTestCase` line are all back. The
`Unrecognized option` error from the newer knpu version is a real problem to solve during the
Symfony 7 upgrade (see Chapter 2).

### src/Guzzle middlewares

`StaleLoggingMiddleware`, `StatusDateOverrideMiddleware`, and `SubjectRewritingMiddleware` in
`src/Guzzle/` were dead code -- the `GuzzleMiddlewarePass` that registered them was already
commented out. Their Symfony HttpClient equivalents live in `src/HttpClient/`. Deleted along with
their tests in `test/Guzzle/`.

---

## Chapter 2: Replace the GuzzleToSymfonyAdapter OAuth mock with something knpu v2-compatible

When knpu is upgraded (required for Symfony 7), the `http_client` config option will be gone.
The adapter and the entire mock chain described in Chapter 1 will need to be replaced.

The knpu bundle's HTTP calls ultimately go through `league/oauth2-client`, which exposes
`AbstractProvider::setHttpClient(GuzzleHttp\ClientInterface $client)`. The knpu bundle's
`OAuth2Client` wraps the provider and the `OAuthClientPass` already has a commented-out example
of injecting a client via `addMethodCall('setHttpClient', [...])`. That is the correct hook --
inject a Guzzle client backed by a mock handler in the test environment.

Options:
- Register a `GuzzleHttp\HandlerStack` + `GuzzleHttp\Handler\MockHandler` as services and
  populate them in `AppKernelTestCase::mockApiResponse()` alongside the other clients.
- Or, if the upgraded knpu/league stack supports PSR-18, use a PSR-18 mock client instead.

Until this is solved the test suite requires the Symfony 3 knpu version.

---

## Chapter 3: Remove CiviCRM / ContentAlertsController

CiviCRM is no longer in use. The main `/content-alerts` entry points are already redirected to
HubSpot via nginx. The remaining PHP routes (`unsubscribe`, `optout`, `update`, `expired`) also
go through `CiviCrmClient` and can be removed once confirmed no longer needed.

**Files to delete:**
- `src/Controller/ContentAlertsController.php`
- `test/Controller/ContentAlertsControllerTest.php`
- `src/Form/Type/ContentAlertsType.php`
- `src/Form/Type/ContentAlertsUnsubscribeType.php`
- `src/Form/Type/ContentAlertsOptoutType.php`
- `src/Form/Type/ContentAlertsUpdateRequestType.php`
- `elife/civi-contacts` from `composer.json`
- `CiviCrmClient` service from `app/config/services.yml`
- `MockCiviCrmClient` from `app/config/services_dev.yml`
- All `content-alerts-*` routes from `app/config/routing.yml`

**Files to update** (generate URLs for the `content-alerts` route -- will throw once the route is gone):
- `src/ViewModel/Factory/SiteHeaderFactory.php` -- "Alerts" nav item
- `src/ViewModel/Factory/FooterFactory.php` -- "Subscribe to alerts" footer link
- `src/Controller/Controller.php` -- "Sign up for email alerts" button
- `src/Controller/AlertsController.php` -- 3 links to content-alerts variants
- `src/Controller/SubjectsController.php` -- inline "subscribe to email alerts" link

These should point to the HubSpot URL (via a config parameter) or be removed, depending on
whether HubSpot provides equivalent subscribe/unsubscribe entry points.
