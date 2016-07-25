<?php

namespace test\eLife\Journal\Controller;

final class TermsControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_terms_and_policy_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/terms');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Terms and policy', $crawler->filter('.content-header__title')->text());
    }

    protected function getUrl() : string
    {
        return '/terms';
    }
}
