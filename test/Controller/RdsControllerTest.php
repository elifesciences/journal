<?php

namespace test\eLife\Journal\Controller;

use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Uri;
use test\eLife\Journal\WebTestCase;
use function GuzzleHttp\Psr7\parse_query;

/**
 * @backupGlobals enabled
 */
final class RdsControllerTest extends WebTestCase {
  /**
   * @before
   */
  public function enableFeatureFlag() {
    $_ENV['FEATURE_RDS'] = TRUE;
  }

  /**
   * @test
   */
  public function it_does_not_show_rds_article_if_the_feature_flag_is_disabled() {
    $_ENV['FEATURE_RDS'] = FALSE;

    $client = static::createClient();

    // TODO: verify what the actual id should be for ci for an rds article
    $client->request('GET', '/articles/16846');

    $this->assertSame(404, $client->getResponse()->getStatusCode());
  }

  /**
   * @test
   */
  public function it_does_not_show_rds_article_if_the_feature_flag_is_enabled_but_the_article_has_no_rds() {
    $client = static::createClient();

    $client->request('GET', '/articles/26231/rds');

    $this->assertSame(404, $client->getResponse()->getStatusCode());
  }

  /**
   * @test
   */
  public function it_does_shows_the_rds_article_if_the_feature_flag_is_enabled_and_the_article_has_rds() {
    $client = static::createClient();

    $client->request('GET', '/articles/26231/rds');

    $this->assertSame(200, $client->getResponse()->getStatusCode());
  }
}
