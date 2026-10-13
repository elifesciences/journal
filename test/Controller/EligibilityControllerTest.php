<?php

namespace test\eLife\Journal\Controller;

final class EligibilityControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_eligibility_search_form()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $checker = $crawler->filter('.institution-eligibility-checker');
        $this->assertCount(1, $checker);
        $this->assertSame('/eligibility.json', $checker->attr('data-institutions-url'));
        $this->assertCount(1, $checker->selectButton('Search'));
    }

    /**
     * @test
     */
    public function it_shows_an_agreed_hhmi_outcome_for_hhmi()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/eligibility/check?institution='.urlencode('Howard Hughes Medical Institute'));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('.institution-eligibility-outcome--agreed-hhmi'));
    }

    /**
     * @test
     */
    public function it_matches_institutions_case_insensitively()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/eligibility/check?institution='.urlencode('agreed university'));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('.institution-eligibility-outcome--agreed'));
    }

    /**
     * @test
     */
    public function it_shows_a_not_agreed_outcome_for_a_known_institution()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/eligibility/check?institution='.urlencode('Not Agreed University'));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('.institution-eligibility-outcome--not-agreed-published'));
    }

    /**
     * @test
     */
    public function it_shows_possible_matches_for_an_unrecognised_but_similar_institution()
    {
        $client = static::createClient();

        // Misspelled Sheffield
        $crawler = $client->request('GET', '/eligibility/check?institution='.urlencode('Sheffild'));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('.institution-eligibility-outcome--not-agreed-not-published'));

        $matches = $crawler->filter('.institution-eligibility-outcome__possible_matches_link');
        $this->assertCount(1, $matches);
        $this->assertContains('University of Sheffield', $this->crawlerText($matches));
        $this->assertSame('/eligibility/check?institution=University+of+Sheffield', $matches->attr('href'));
    }

    /**
     * @test
     */
    public function it_shows_no_possible_matches_for_a_completely_unrecognised_institution()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/eligibility/check?institution='.urlencode('Qwzxjklmnop'));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('.institution-eligibility-outcome--not-agreed-not-published'));
        $this->assertCount(0, $crawler->filter('.institution-eligibility-outcome__possible_matches_link'));
    }

    protected function getUrl() : string
    {
        return '/eligibility';
    }
}
