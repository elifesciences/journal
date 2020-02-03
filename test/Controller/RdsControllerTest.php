<?php

namespace test\eLife\Journal\Controller;

use test\eLife\Journal\WebTestCase;

/**
 * @backupGlobals enabled
 */
final class RdsControllerTest extends WebTestCase
{
    /**
     * @before
     */
    public function enableFeatureFlag()
    {
        $_ENV['FEATURE_RDS'] = true;
    }

    /**
     * @test
     */
    public function it_does_not_show_rds_article_if_the_feature_flag_is_disabled()
    {
        $_ENV['FEATURE_RDS'] = false;

        $client = static::createClient();

        // TODO: verify what the actual id should be for ci for an rds article
        $client->request('GET', '/articles/16846');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_does_not_show_rds_article_if_the_feature_flag_is_enabled_but_the_article_has_no_rds()
    {
        $client = static::createClient();

        $client->request('GET', '/articles/26231/rds');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_does_shows_the_rds_article_if_the_feature_flag_is_enabled_and_the_article_has_rds()
    {
        $client = static::createClient();

        $client->request('GET', '/articles/26231/rds');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }
}
