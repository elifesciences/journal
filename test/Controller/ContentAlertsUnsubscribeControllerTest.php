<?php

namespace test\eLife\Journal\Controller;

final class ContentAlertsUnsubscribeControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_content_alerts_unsubscribe_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Unsubscribe from this mailing', $crawler->filter('h1')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Unsubscribe from this mailing | eLife', $crawler->filter('title')->text());
        $this->assertSame('/content-alerts/unsubscribe/green', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/content-alerts/unsubscribe/green', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Unsubscribe from this mailing', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[property="og:description"]'));
        $this->assertEmpty($crawler->filter('meta[name="description"]'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-600x600@1.png'), '/'), $crawler->filter('meta[name="twitter:image"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-600x600@1.png'), '/'), $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.identifier"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.relation.ispartof"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.title"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.description"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.date"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.rights"]'));
    }

    /**
     * @test
     */
    public function it_displays_confirmation_message()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $form = $crawler->selectButton('Unsubscribe')->form();

        $crawler = $client->submit($form);

        $this->assertSame('Unsubscribed', $crawler->filter('h1')->text());
        $this->assertSame('You are no longer subscribed to the latest research articles from eLife.', $crawler->filter('#content p')->text());
        $this->assertSame('Back to Homepage', $crawler->filter('#content a')->text());
        $this->assertSame('/', $crawler->filter('#content a')->attr('href'));
    }

    /**
     * @test
     */
    public function it_does_not_allow_you_to_unsubscribe_if_already()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl('green/early-career'));

        $this->assertSame('Something went wrong', $crawler->filter('h1')->text());
    }

    /**
     * @test
     */
    public function it_does_not_allow_you_to_unsubscribe_if_optout()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl('red'));

        $this->assertSame('Something went wrong', $crawler->filter('h1')->text());
    }

    /**
     * @test
     */
    public function it_has_a_csrf_token_if_you_are_logged_in()
    {
        $client = static::createClient();

        $this->logIn($client);

        $crawler = $client->request('GET', $this->getUrl());

        $form = $crawler->selectButton('Unsubscribe')->form();

        $this->assertTrue($form->has('content_alerts_unsubscribe[_token]'));
    }

    /**
     * @test
     */
    public function it_has_a_honeypot()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $form = $crawler->selectButton('Unsubscribe')->form();

        $form["content_alerts_unsubscribe[{$this->getParameter('honeypot_field')}]"] = 'bar@example.com';
        $crawler = $client->submit($form);

        $this->assertCount(1, $crawler->filter('.info-bar'));
        $this->assertSame('Please try submitting the form again.', trim($crawler->filter('.info-bar')->text()));
    }

    /**
     * @test
     */
    public function it_has_the_sign_up_cta()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $emailCta = $crawler->filter('.email-cta');

        $this->assertCount(0, $emailCta);
    }

    /**
     * @test
     */
    public function it_has_the_footer()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $footer = $crawler->filter('footer.site-footer');

        $this->assertCount(0, $footer);
    }

    protected function getUrl(string $identifier = 'green') : string
    {
        return '/content-alerts/unsubscribe/'.$identifier;
    }
}
