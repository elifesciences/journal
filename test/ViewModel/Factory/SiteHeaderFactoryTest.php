<?php

namespace test\eLife\Journal\ViewModel\Factory;

use eLife\Journal\ViewModel\Factory\SiteHeaderFactory;
use eLife\Patterns\ViewModel\SiteHeader;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use test\eLife\Journal\KernelTestCase;

final class SiteHeaderFactoryTest extends KernelTestCase
{
    /**
     * @var SiteHeaderFactory
     */
    private $siteHeaderFactory;

    /**
     * @before
     */
    public function createSiteHeaderFactory()
    {
        static::bootKernel();

        $this->siteHeaderFactory = static::$kernel->getContainer()->get('elife.journal.view_model.factory.site_header');
    }

    /**
     * @test
     */
    public function it_returns_a_site_header()
    {
        $this->assertInstanceOf(SiteHeader::class, $this->siteHeaderFactory->createSiteHeader());
    }

    /**
     * @test
     */
    public function it_links_directly_to_the_submit_site_when_the_feature_flag_is_disabled()
    {
        $siteHeader = $this->siteHeaderFactory->createSiteHeader();

        $this->assertSame('http://submit.elifesciences.org/path', $siteHeader['secondaryLinks']['linkedItems'][2]['button']['path']);
    }

    /**
     * @test
     * @backupGlobals enabled
     */
    public function it_links_directly_to_the_submit_site_when_the_feature_flag_is_enable_and_you_are_not_logged_in()
    {
        $_ENV['FEATURE_XPUB'] = true;

        // Required to enable the authorization checker
        $tokenStorage = static::$kernel->getContainer()->get('security.token_storage');
        $tokenStorage->setToken(new AnonymousToken('secret', 'anon.'));

        $siteHeader = $this->siteHeaderFactory->createSiteHeader();

        $this->assertSame('http://submit.elifesciences.org/path', $siteHeader['secondaryLinks']['linkedItems'][2]['button']['path']);
    }

    /**
     * @test
     * @backupGlobals enabled
     */
    public function it_links_to_the_submit_route_when_the_feature_flag_is_enable_and_you_are_logged_in()
    {
        $_ENV['FEATURE_XPUB'] = true;

        // Required to enable the authorization checker
        $tokenStorage = static::$kernel->getContainer()->get('security.token_storage');
        $tokenStorage->setToken(new PostAuthenticationGuardToken(new OAuthUser('jcarberry', $roles = ['ROLE_USER', 'ROLE_OAUTH_USER']), 'main', $roles));

        $siteHeader = $this->siteHeaderFactory->createSiteHeader();

        $this->assertSame('/submit', $siteHeader['secondaryLinks']['linkedItems'][2]['button']['path']);
    }
}
