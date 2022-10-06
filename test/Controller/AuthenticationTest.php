<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use Symfony\Component\BrowserKit\Cookie;
use test\eLife\Journal\WebTestCase;
use Traversable;
use function GuzzleHttp\Psr7\build_query;
use function GuzzleHttp\Psr7\parse_query;

final class AuthenticationTest extends WebTestCase
{
    /**
     * @test
     * @dataProvider refererProvider
     */
    public function it_uses_the_referer_header_for_redirecting_after_logging_in(string $referer, string $expectedRedirect)
    {
        $client = static::createClient();
        $client->followRedirects(false);

        $client->request('GET', '/log-in', [], [], array_filter(['HTTP_REFERER' => $referer]));
        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect());

        $state = parse_query((new Uri($response->headers->get('Location')))->getQuery())['state'];

        $this->readyToken();

        $client->request('GET', "/log-in/check?code=foo&state=$state");
        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect());
        $this->assertSameUri($expectedRedirect, $response->headers->get('Location'));
    }

    public function refererProvider() : Traversable
    {
        yield 'no header' => ['', 'http://localhost/'];
        yield 'string header' => ['foo', 'http://localhost/'];
        yield 'route-name header' => ['about', 'http://localhost/'];
        yield 'invalid uri header' => ['http://', 'http://localhost/'];
        yield 'homepage' => ['http://localhost/', 'http://localhost/'];
        yield 'local path' => ['http://localhost/foo', 'http://localhost/foo'];
        yield 'local path with query string' => ['http://localhost/foo?bar', 'http://localhost/foo?bar'];
        yield 'external site' => ['http://www.example.com/', 'http://localhost/'];
        yield 'external site with path' => ['http://www.example.com/foo', 'http://localhost/'];
    }

    /**
     * @test
     */
    public function it_shows_error_messages()
    {
        $client = static::createClient();

        $client->followRedirects(false);

        $this->readyHomePage();

        $client->request('GET', '/log-in');
        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect());

        $location = Uri::withoutQueryValue(new Uri($response->headers->get('Location')), 'state');
        $this->assertSameUri('http://api.elifesciences.org/oauth2/authorize?response_type=code&client_id=journal_client_id&redirect_uri=http%3A%2F%2Flocalhost%2Flog-in%2Fcheck', $location);

        $state = parse_query((new Uri($response->headers->get('Location')))->getQuery())['state'];

        $client->followRedirects();

        $this->mockApiResponse(
            new Request(
                'POST',
                'http://api.elifesciences.org/oauth2/token',
                ['Content-Type' => 'application/x-www-form-urlencoded'],
                build_query(['code' => 'foo', 'grant_type' => 'authorization_code', 'client_id' => 'journal--local-id', 'client_secret' => 'journal--local-secret', 'redirect_uri' => 'http://localhost/log-in/check'])
            ),
            new Response(
                400,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'error' => 'invalid_request',
                ])
            )
        );

        $crawler = $client->request('GET', "/log-in/check?code=foo&state=$state");

        $this->assertCount(1, $crawler->filter('.info-bar'));
        $this->assertSame('Failed to log in, please try again.', trim($crawler->filter('.info-bar')->text()));
        $this->assertSame('max-age=0, must-revalidate, private', $client->getResponse()->headers->get('Cache-Control'));
        $this->assertEmpty($client->getResponse()->getVary());
        $this->assertEmpty($client->getCookieJar()->all());
    }

    /**
     * @test
     */
    public function it_redirects_to_help_page_when_no_name_is_available()
    {
        $client = static::createClient();

        $client->followRedirects(false);

        $this->readyHomePage();

        $client->request('GET', '/log-in');
        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect());

        $location = Uri::withoutQueryValue(new Uri($response->headers->get('Location')), 'state');
        $this->assertSameUri('http://api.elifesciences.org/oauth2/authorize?response_type=code&client_id=journal_client_id&redirect_uri=http%3A%2F%2Flocalhost%2Flog-in%2Fcheck', $location);

        $state = parse_query((new Uri($response->headers->get('Location')))->getQuery())['state'];

        $this->mockApiResponse(
            new Request(
                'POST',
                'http://api.elifesciences.org/oauth2/token',
                ['Content-Type' => 'application/x-www-form-urlencoded'],
                build_query(['code' => 'foo', 'grant_type' => 'authorization_code', 'client_id' => 'journal--local-id', 'client_secret' => 'journal--local-secret', 'redirect_uri' => 'http://localhost/log-in/check'])
            ),
            new Response(
                400,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'error' => 'invalid_request',
                    'error_description' => 'No name visible',
                ])
            )
        );

        $client->request('GET', "/log-in/check?code=foo&state=$state");

        $client->followRedirect();

        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/log-in/orcid-visibility-setting'));
    }

    /**
     * @test
     */
    public function it_logs_you_out_if_your_profile_is_unavailable()
    {
        $client = static::createClient();

        $client->followRedirects();

        $this->logIn($client);

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/profiles/jcarberry',
                ['Accept' => 'application/vnd.elife.profile+json; version=1']
            ),
            new Response(
                404,
                [
                    'Content-Type' => 'application/problem+json',
                ],
                json_encode([
                    'title' => 'Not found',
                ])
            )
        );

        $this->readyHomePage();

        $client->followRedirects(false);

        $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/log-out'));

        $client->followRedirect();

        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/'));

        $crawler = $client->followRedirect();

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getCookieJar()->all());
    }

    /**
     * @test
     */
    public function it_removes_the_cookie_if_the_session_does_not_exist()
    {
        $client = static::createClient();

        $session = $client->getContainer()->get('session');

        $cookie = new Cookie($session->getName(), 'foo', null, null, 'localhost');
        $client->getCookieJar()->set($cookie);

        $this->readyHomePage();

        $crawler = $client->request('GET', '/');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getCookieJar()->all());
    }

    /**
     * @test
     */
    public function it_lets_you_log_out_and_redirects_you_to_the_homepage()
    {
        $client = static::createClient();

        $client->followRedirects();

        $this->logIn($client);

        $this->readyHomePage();
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/annotations?by=jcarberry&page=1&per-page=10&order=desc&use-date=updated&access=restricted',
                ['Accept' => 'application/vnd.elife.annotation-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.annotation-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        $crawler = $client->request('GET', '/about');

        $crawler = $client->click($crawler->filter('a:contains("Josiah Carberry")')->link());
        $crawler = $client->click($crawler->filter('a:contains("Log out")')->link());

        $this->assertEquals('http://localhost/', $crawler->getUri());
        $this->assertEmpty($client->getCookieJar()->all());
    }

    private function readyHomePage()
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=10&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-communication&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=2'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                    'subjects' => [
                        [
                            'id' => 'subject',
                            'name' => 'Some subject',
                            'results' => 0,
                        ],
                    ],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-communication' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'review-article' => 0,
                        'scientific-correspondence' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-post' => 0,
                        'podcast-episode' => 0,
                        'reviewed-preprint' => 0,
                    ],
                ])
            )
        );
    }
}
