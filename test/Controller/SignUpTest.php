<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\WebTestCase;

final class SignUpTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_shows_result_messages()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('max-age=300, public, stale-if-error=86400, stale-while-revalidate=300', $client->getResponse()->headers->get('Cache-Control'));
        $this->assertSame(['Cookie'], $client->getResponse()->getVary());

        $form = $crawler->selectButton('Sign up')->form();
        $form['email_cta[email]'] = 'foo@example.com';

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://crm.elifesciences.org/crm/civicrm/profile/create?reset=1&gid=18'
            ),
            new Response(
                200,
                ['Content-Type' => 'text/html'],
                '<html>
<body>
<form action="/crm/civicrm/profile/create" method="post">
<input type="text" name="email-3">
<input type="submit" value="Save">
</form>
</body>
</html>'
            )
        );

        $this->mockApiResponse(
            new Request(
                'POST',
                'http://crm.elifesciences.org/crm/civicrm/profile/create',
                ['Content-Type' => 'application/x-www-form-urlencoded'],
                'email-3=foo%40example.com'
            ),
            new Response(
                200,
                ['Content-Type' => 'text/html'],
                '<html>
<body>
<span class="messages">Your subscription request has been submitted</span>
</body>
</html>'
            )
        );
        $client->submit($form);
        $crawler = $client->followRedirect();

        $this->assertCount(1, $crawler->filter('.info-bar'));
        $this->assertSame('Almost finished! Click the link in the email we just sent you to confirm your subscription.', trim($crawler->filter('.info-bar')->text()));

        $this->assertSame('max-age=0, must-revalidate, private', $client->getResponse()->headers->get('Cache-Control'));
        $this->assertEmpty($client->getResponse()->getVary());
        $this->assertEmpty($client->getCookieJar()->all());
    }

    /**
     * @test
     */
    public function it_shows_error_messages()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('max-age=300, public, stale-if-error=86400, stale-while-revalidate=300', $client->getResponse()->headers->get('Cache-Control'));
        $this->assertSame(['Cookie'], $client->getResponse()->getVary());

        $crawler = $client->submit($crawler->selectButton('Sign up')->form());

        $this->assertCount(1, $crawler->filter('.info-bar'));
        $this->assertSame('There were problems submitting the form.', trim($crawler->filter('.info-bar')->text()));
        $this->assertSame('Please provide your email address.', $crawler->filter('.compact-form__message')->text());

        $this->assertSame('max-age=0, must-revalidate, private', $client->getResponse()->headers->get('Cache-Control'));
        $this->assertEmpty($client->getResponse()->getVary());

        $client->request('GET', $this->getUrl());
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('max-age=300, public, stale-if-error=86400, stale-while-revalidate=300', $client->getResponse()->headers->get('Cache-Control'));
        $this->assertSame(['Cookie'], $client->getResponse()->getVary());
    }

    /**
     * @test
     */
    public function it_has_a_csrf_token_if_you_are_logged_in()
    {
        $client = static::createClient();

        $this->logIn($client);

        $crawler = $client->request('GET', $this->getUrl());

        $form = $crawler->selectButton('Sign up')->form();

        $this->assertTrue($form->has('email_cta[_token]'));
    }

    /**
     * @test
     */
    public function it_has_a_honeypot()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $form = $crawler->selectButton('Sign up')->form();
        $form['email_cta[email]'] = 'foo@example.com';
        $form["email_cta[{$this->getParameter('honeypot_field')}]"] = 'bar@example.com';
        $crawler = $client->submit($form);

        $this->assertCount(1, $crawler->filter('.info-bar'));
        $this->assertSame('Please try submitting the form again.', trim($crawler->filter('.info-bar')->text()));
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=10&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-communication&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                    'subjects' => [],
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

        return '/';
    }
}
