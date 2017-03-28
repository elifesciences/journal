<?php

namespace test\eLife\Journal\Controller;

final class ContactControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_contact_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/contact');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Contact', $crawler->filter('main h1')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Contact | eLife', $crawler->filter('title')->text());
        $this->assertSame('/contact', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/contact', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Contact', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
    }

    /**
     * @test
     */
    public function it_has_cache_headers()
    {
        $client = static::createClient();

        $client->request('GET', $this->getUrl());

        $this->assertSame('must-revalidate, no-cache, no-store, private', $client->getResponse()->headers->get('Cache-Control'));
        $this->assertEmpty($client->getResponse()->getVary());
    }

    /**
     * @test
     */
    public function it_requires_all_the_fields_to_be_completed()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/contact');

        $crawler = $client->submit($crawler->selectButton('Submit')->form());

        $this->assertCount(3, $crawler->filter('.info-bar'));
        $this->assertSame('Please provide your name.', trim($crawler->filter('.info-bar')->eq(0)->text()));
        $this->assertSame('Please provide your email address.', trim($crawler->filter('.info-bar')->eq(1)->text()));
        $this->assertSame('Please let us know your question.', trim($crawler->filter('.info-bar')->eq(2)->text()));
    }

    /**
     * @test
     */
    public function it_requires_a_valid_email()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/contact');

        $form = $crawler->selectButton('Submit')->form();
        $form['contact[name]'] = 'My name';
        $form['contact[email]'] = 'foo';
        $form['contact[question]'] = 'My question';

        $crawler = $client->submit($form);

        $this->assertCount(1, $crawler->filter('.info-bar'));
        $this->assertSame('Please provide a valid email address.', trim($crawler->filter('.info-bar')->text()));
    }

    protected function getUrl() : string
    {
        return '/contact';
    }
}
