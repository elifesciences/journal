<?php

namespace test\eLife\Journal\Controller;

use Traversable;
use TRegx\DataProvider\DataProviders;

final class ContentAlertsControllerTest extends PageTestCase
{
    /**
     * @test
     * @dataProvider providerVariants
     */
    public function it_displays_the_content_alerts_page(string $url)
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $url);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Subscribe to eLife\'s email alerts', $crawler->filter('main h1')->text());
    }

    /**
     * @test
     * @dataProvider providerVariants
     */
    public function it_has_metadata(string $url)
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $url.'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Subscribe to eLife\'s email alerts | eLife', $crawler->filter('title')->text());
        $this->assertSame($url, $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost'.$url, $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Subscribe to eLife\'s email alerts', $crawler->filter('meta[property="og:title"]')->attr('content'));
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
     * @dataProvider providerVariants
     */
    public function it_requires_email_and_preferences_fields_to_be_completed(string $url)
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $url);

        $form = $crawler->selectButton('Subscribe')->form();
        $form['content_alerts[preferences][0]']->untick();

        $crawler = $client->submit($form);

        $this->assertCount(1, $crawler->filter('.info-bar'));
        $this->assertSame('There were problems submitting the form.', trim($crawler->filter('.info-bar')->text()));
        $this->assertSame(
            ['Please provide your email address.', 'Please select an email type to subscribe.'],
            array_map('trim', $crawler->filter('.form-item__message')->extract(['_text']))
        );
    }

    /**
     * @test
     * @dataProvider providerVariants
     */
    public function it_requires_a_valid_email(string $url)
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $url);

        $form = $crawler->selectButton('Subscribe')->form();
        $form['content_alerts[email]'] = 'foo';

        $crawler = $client->submit($form);

        $this->assertCount(1, $crawler->filter('.info-bar'));
        $this->assertSame('There were problems submitting the form.', trim($crawler->filter('.info-bar')->text()));
        $this->assertSame(
            ['Please provide a valid email address.'],
            array_map('trim', $crawler->filter('.form-item__message')->extract(['_text']))
        );
    }

    /**
     * @test
     * @dataProvider contactProvider
     */
    public function it_displays_confirmation_message(string $url, string $email)
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $url);

        $form = $crawler->selectButton('Subscribe')->form();
        $form['content_alerts[email]'] = $email;

        $crawler = $client->submit($form);

        $this->assertSame('Thank you for subscribing!', $crawler->filter('#thank-you h2')->text());
        $this->assertSame("A confirmation email has been sent to {$email}.", $crawler->filter('#thank-you p')->text());
        $this->assertSame('Back to Homepage', $crawler->filter('#thank-you a')->text());
        $this->assertSame('/', $crawler->filter('#thank-you a')->attr('href'));
    }

    /**
     * @test
     * @dataProvider providerVariants
     */
    public function it_triggers_update_preferences_message(string $url)
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $url);

        $form = $crawler->selectButton('Subscribe')->form();
        $form['content_alerts[email]'] = 'green@example.com';

        $crawler = $client->submit($form);

        $this->assertSame('You are already subscribed', $crawler->filter('#thank-you h2')->text());
        $this->assertSame('An email has been sent to green@example.com.', $crawler->filter('#thank-you p')->text());
        $this->assertSame('Back to Homepage', $crawler->filter('#thank-you a')->text());
        $this->assertSame('/', $crawler->filter('#thank-you a')->attr('href'));
    }

    /**
     * @test
     * @dataProvider providerVariants
     */
    public function it_has_a_csrf_token_if_you_are_logged_in(string $url)
    {
        $client = static::createClient();

        $this->logIn($client);

        $crawler = $client->request('GET', $url);

        $form = $crawler->selectButton('Subscribe')->form();

        $this->assertTrue($form->has('content_alerts[_token]'));
    }

    /**
     * @test
     * @dataProvider providerVariants
     */
    public function it_has_a_honeypot(string $url)
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $url);

        $form = $crawler->selectButton('Subscribe')->form();
        $form['content_alerts[email]'] = 'foo@example.com';

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

    public function providerVariants() : Traversable
    {
        yield 'default' => [$this->getUrl()];
        yield 'early-career' => [$this->getUrl('early-career')];
        yield 'technology' => [$this->getUrl('technology')];
        yield 'elife-newsletter' => [$this->getUrl('elife-newsletter')];
    }

    public function contactProvider() : array
    {
        return DataProviders::cross(
            iterator_to_array($this->providerVariants()),
            [
                'no existing contact' => ['foo@bar.com'],
                'existing contact - new subscriber' => ['amber@example.com'],
                'existing contact - opt out' => ['red@example.com'],
            ]
        );
    }

    protected function getUrl(string $variant = null) : string
    {
        return '/content-alerts'.($variant ? '/'.$variant : '');
    }
}
