<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use test\eLife\Journal\WebTestCase;
use function GuzzleHttp\Psr7\build_query;
use function GuzzleHttp\Psr7\parse_query;

final class AuthenticationTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_does_not_let_you_log_in_when_the_feature_flag_is_disabled()
    {
        $client = static::createClient();

        $this->readyHomePage();

        $crawler = $client->request('GET', '/');

        $this->assertNotContains('Log in/Register', $crawler->text());
        $this->assertNotContains('Log out', $crawler->text());

        $client->request('GET', '/log-in');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_lets_you_log_in_when_the_feature_flag_is_enabled()
    {
        $client = static::createClient();

        $client->followRedirects(false);

        $this->readyHomePage();

        $crawler = $client->request('GET', '/?open-sesame');

        $client->click($crawler->filter('a:contains("Log in/Register")')->link());

        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect());

        $location = Uri::withoutQueryValue(new Uri($response->headers->get('Location')), 'state');
        $this->assertSame('http://api.elifesciences.org/oauth2/authorize?response_type=code&client_id=journal_client_id&redirect_uri=http%3A%2F%2Flocalhost%2Flog-in%2Fcheck', $location->__toString());

        $state = parse_query((new Uri($response->headers->get('Location')))->getQuery())['state'];

        $client->followRedirects();

        $this->readyToken();

        $crawler = $client->request('GET', "/log-in/check?code=foo&state=$state");

        $this->assertContains('Log out', $crawler->text());
        $this->assertNotContains('Log in/Register', $crawler->text());
    }

    /**
     * @test
     */
    public function it_shows_error_messages()
    {
        $client = static::createClient();

        $client->followRedirects(false);

        $this->readyHomePage();

        $crawler = $client->request('GET', '/?open-sesame');

        $client->click($crawler->filter('a:contains("Log in/Register")')->link());

        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect());

        $location = Uri::withoutQueryValue(new Uri($response->headers->get('Location')), 'state');
        $this->assertSame('http://api.elifesciences.org/oauth2/authorize?response_type=code&client_id=journal_client_id&redirect_uri=http%3A%2F%2Flocalhost%2Flog-in%2Fcheck', $location->__toString());

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

        $this->assertNotContains('Log out', $crawler->text());
        $this->assertContains('Log in/Register', $crawler->text());

        $this->assertCount(1, $crawler->filter('.info-bar'));
        $this->assertSame('Failed to log in, please try again.', trim($crawler->filter('.info-bar')->text()));
        $this->assertSame('no-cache, private', $client->getResponse()->headers->get('Cache-Control'));
        $this->assertEmpty($client->getResponse()->getVary());

        $crawler = $client->reload();
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(0, $crawler->filter('.info-bar'));
    }

    /**
     * @test
     */
    public function it_shows_an_error_message_when_no_name_is_available()
    {
        $client = static::createClient();

        $client->followRedirects(false);

        $this->readyHomePage();

        $crawler = $client->request('GET', '/?open-sesame');

        $client->click($crawler->filter('a:contains("Log in/Register")')->link());

        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect());

        $location = Uri::withoutQueryValue(new Uri($response->headers->get('Location')), 'state');
        $this->assertSame('http://api.elifesciences.org/oauth2/authorize?response_type=code&client_id=journal_client_id&redirect_uri=http%3A%2F%2Flocalhost%2Flog-in%2Fcheck', $location->__toString());

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
                    'error_description' => 'No name visible',
                ])
            )
        );

        $crawler = $client->request('GET', "/log-in/check?code=foo&state=$state");

        $this->assertNotContains('Log out', $crawler->text());
        $this->assertContains('Log in/Register', $crawler->text());

        $this->assertCount(1, $crawler->filter('.info-bar'));
        $this->assertSame('Please adjust your ORCID privacy settings for eLife to display your name.', trim($crawler->filter('.info-bar')->text()));
        $this->assertSame('no-cache, private', $client->getResponse()->headers->get('Cache-Control'));
        $this->assertEmpty($client->getResponse()->getVary());

        $crawler = $client->reload();
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(0, $crawler->filter('.info-bar'));
    }

    /**
     * @test
     */
    public function it_disables_the_feature_flag_when_you_log_out()
    {
        $client = static::createClient();

        $client->followRedirects();

        $this->logIn($client);

        $this->readyHomePage();

        $crawler = $client->request('GET', '/');

        $crawler = $client->click($crawler->filter('a:contains("Log out")')->link());

        $this->assertNotContains('Log out', $crawler->text());
        $this->assertNotContains('Log in/Register', $crawler->text());
    }

    private function readyHomePage()
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=10&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
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
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'scientific-correspondence' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-post' => 0,
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );
    }

    private function readyToken()
    {
        $this->mockApiResponse(
            new Request(
                'POST',
                'http://api.elifesciences.org/oauth2/token',
                ['Content-Type' => 'application/x-www-form-urlencoded'],
                build_query(['code' => 'foo', 'grant_type' => 'authorization_code', 'client_id' => 'journal--local-id', 'client_secret' => 'journal--local-secret', 'redirect_uri' => 'http://localhost/log-in/check'])
            ),
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'access_token' => 'token',
                    'expires_in' => 3920,
                    'token_type' => 'Bearer',
                    'orcid' => '0000-0002-1825-0097',
                    'name' => 'Josiah Carberry',
                ])
            )
        );
    }
}
