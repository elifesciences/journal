<?php

namespace test\eLife\Journal\Controller;

final class ContentAlertsUpdateControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_content_alerts_update_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Your email preferences', $crawler->filter('h1')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Your email preferences | eLife', $crawler->filter('title')->text());
        $this->assertSame('/content-alerts/green', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/content-alerts/green', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Your email preferences', $crawler->filter('meta[property="og:title"]')->attr('content'));
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
    public function it_hides_the_email_field()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $email = $crawler->filter('input[name="content_alerts[email]"]');

        $this->assertEquals('hidden', $email->attr('type'));
        $this->assertEquals('green@example.com', $email->attr('value'));
    }

    /**
     * @test
     */
    public function it_hides_the_contact_id_field()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $email = $crawler->filter('input[name="content_alerts[contact_id]"]');

        $this->assertEquals('hidden', $email->attr('type'));
        $this->assertEquals('12345', $email->attr('value'));
    }

    /**
     * @test
     */
    public function it_hides_the_groups_before_changes_field()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $email = $crawler->filter('input[name="content_alerts[groups]"]');

        $this->assertEquals('hidden', $email->attr('type'));
        $this->assertEquals('latest_articles', $email->attr('value'));
    }

    /**
     * @test
     */
    public function it_requires_preferences_fields_to_be_completed()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $form = $crawler->selectButton('Update')->form();
        $form['content_alerts[preferences][0]']->untick();
        $form['content_alerts[preferences][1]']->untick();
        $form['content_alerts[preferences][2]']->untick();

        $crawler = $client->submit($form);

        $this->assertSame(
            ['Please select an email type to subscribe.'],
            array_map('trim', $crawler->filter('.form-item__message')->extract(['_text']))
        );
    }

    /**
     * @test
     */
    public function it_redirects_if_invalid_identifier()
    {
        $client = static::createClient();

        $client->request('GET', $this->getUrl('invalid'));

        $response = $client->getResponse();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/content-alerts/expired', $response->headers->get('Location'));
    }

    /**
     * @test
     */
    public function it_displays_confirmation_message()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $form = $crawler->selectButton('Update')->form();

        $crawler = $client->submit($form);

        $this->assertSame('Thank you', $crawler->filter('h1')->text());
        $this->assertSame('Email preferences for green@example.com have been updated.', $crawler->filter('#content p')->text());
        $this->assertSame('Back to Homepage', $crawler->filter('#content a')->text());
        $this->assertSame('/', $crawler->filter('#content a')->attr('href'));
    }

    /**
     * @test
     */
    public function it_has_a_csrf_token_if_you_are_logged_in()
    {
        $client = static::createClient();

        $this->logIn($client);

        $crawler = $client->request('GET', $this->getUrl());

        $form = $crawler->selectButton('Update')->form();

        $this->assertTrue($form->has('content_alerts[_token]'));
    }

    /**
     * @test
     */
    public function it_has_a_honeypot()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $form = $crawler->selectButton('Update')->form();

        $form["content_alerts[{$this->getParameter('honeypot_field')}]"] = 'bar@example.com';
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
        return '/content-alerts/'.$identifier;
    }
}
