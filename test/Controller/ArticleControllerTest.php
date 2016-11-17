<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\ArticleFixture;
use test\eLife\Journal\Page\ArticlePage;

final class ArticleControllerTest extends PageTestCase
{
    /**
     * @before
     */
    public function setUpFixture()
    {
        $this->fixture = new ArticleFixture();
    }

    /**
     * @test
     */
    public function it_displays_an_article_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $page = new ArticlePage($crawler);
        $this->assertSame('Article title', $page->headerTitle());
        $this->assertSame('Foo Bar', trim($crawler->filter('.content-header__author_list')->text()));
        $this->assertEmpty($crawler->filter('.content-header__institution_list'));

        $this->assertContains('Cite as: eLife 2012;1:e00001', $page->citation());
        $this->assertContains('doi: 10.7554/eLife.00001', $crawler->filter('.contextual-data__cite_wrapper')->text());
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_article_is_not_found()
    {
        $client = static::createClient();

        static::mockApiResponse(
            $this->fixture->articleRequest('00001'),
            $this->fixture->articleNotFoundResponse()
        );

        $client->request('GET', '/content/1/e00001');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_displays_the_author_and_institution_lists()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            $this->fixture->articleRequest('00001'),
            $this->fixture->articleVorResponse('many-authors-and-affiliations')
        );

        $crawler = $client->request('GET', '/content/1/e00001');
        $page = new ArticlePage($crawler);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Article title', $crawler->filter('.content-header__title')->text());
        $this->assertSame(5, $page->headerAuthorCount());
        $this->assertSame('Author One', $page->headerAuthor(0));
        $this->assertSame('Author Two', $page->headerAuthor(1));
        $this->assertSame('Author Three', $page->headerAuthor(2));
        $this->assertSame('Author Four', $page->headerAuthor(3));
        $this->assertSame('on behalf of Institution Four', $page->headerAuthor(4));
        $this->assertSame(3, $page->headerInstitutionCount());
        $this->assertSame('Institution One, Country One', $page->headerInstitution(0));
        $this->assertSame('Institution Two, Country Two', $page->headerInstitution(1));
        $this->assertSame('Institution Three', $page->headerInstitution(2));
    }

    /**
     * @test
     */
    public function it_displays_a_poa()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            $this->fixture->articleRequest('00001'),
            $this->fixture->articlePoaResponse('a-poa')
        );

        $crawler = $client->request('GET', '/content/1/e00001');
        $this->assertContains('Accepted manuscript, PDF only. Full online edition to follow.',
            array_map('trim', $crawler->filter('.info-bar')->extract(['_text'])));
    }

    /**
     * @test
     */
    public function it_displays_content()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            $this->fixture->articleRequest('00001'),
            $this->fixture->articleVorResponse('content')
        );

        $crawler = $client->request('GET', '/content/1/e00001');

        $this->assertSame('Title prefix: Article title', $crawler->filter('.content-header__title')->text());

        $this->assertSame('Abstract',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(1) > header > h2')->text());
        $this->assertSame('Abstract text',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(1) > div > p')->text());
        $this->assertSame('https://doi.org/10.7554/eLife.09560.001',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(1) > div > .doi')->text());
        $this->assertSame('eLife digest',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(2) > header > h2')->text());
        $this->assertSame('Digest text',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(2) > div > p')->text());
        $this->assertSame('https://doi.org/10.7554/eLife.09560.002',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(2) > div > .doi')->text());
        $this->assertSame('Body title',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(3) > header > h2')->text());
        $this->assertSame('Body text',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(3) > div > p')->text());
        $appendix = $crawler->filter('main > .wrapper > div > div > section:nth-of-type(4)');
        $this->assertSame('Appendix 1', $appendix->filter('header > h2')->text());
        $this->assertSame('Appendix title', $appendix->filter('div > section > header > h3')->text());
        $this->assertSame('Appendix text', $appendix->filter('div > p')->text());
        $references = $crawler->filter('main > .wrapper > div > div > section:nth-of-type(5)');
        $this->assertSame('References',
            $references->filter('header > h2')->text());
        $this->assertSame('1',
            $references->filter('div > ol > li:nth-of-type(1) .reference-list__ordinal_number')->text());
        $this->assertSame('Journal article',
            $references->filter('div > ol > li:nth-of-type(1) .reference__title')->text());
        $this->assertSame('Decision letter',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(6) > header > h2')->text());
        $this->assertSame('Decision letter text',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(6) > div > p')->text());
        $this->assertSame('Author response',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(7) > header > h2')->text());
        $this->assertSame('Author response text',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(7) > div > p')->text());

        $articleInfo = $crawler->filter('main > .wrapper > div > div > section:nth-of-type(8)');
        $this->assertSame('Article and author information',
            $articleInfo->filter('header > h2')->text());

        $authorDetails = $articleInfo->filter('div > ol:nth-of-type(1) > li');
        $this->assertCount(1, $authorDetails);
        $this->assertSame('Foo Bar', $authorDetails->eq(0)->filter('.author-details__name')->text());

        $acknowledgements = $articleInfo->filter('div > section:nth-of-type(1)');
        $this->assertSame('Acknowledgements', $acknowledgements->filter('header > h3')->text());
        $this->assertSame('Acknowledgements text', trim($acknowledgements->filter('div')->text()));

        $ethics = $articleInfo->filter('div > section:nth-of-type(2)');
        $this->assertSame('Ethics', $ethics->filter('header > h3')->text());
        $this->assertSame('Ethics text', trim($ethics->filter('div')->text()));

        $copyright = $articleInfo->filter('div > section:nth-of-type(3)');
        $this->assertSame('Copyright', $copyright->filter('header > h3')->text());
        $this->assertContains('© 2012, Bar', $copyright->filter('div')->text());
        $this->assertContains('Copyright statement.', $copyright->filter('div')->text());

        $this->assertSame(
            [
                'Abstract',
                'eLife digest',
                'Body title',
                'Appendix 1',
                'References',
                'Decision letter',
                'Author response',
                'Article and author information',
            ],
            array_map('trim', $crawler->filter('.view-selector__jump_link_item')->extract('_text'))
        );
    }

    /**
     * @test
     */
    public function it_displays_content_without_sections_if_there_are_not_any()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            $this->fixture->articleRequest('00001'),
            $this->fixture->articleVorResponse('content-without-sections')
        );

        $crawler = $client->request('GET', '/content/1/e00001');

        $this->assertNotContains('Body title', $crawler->text());
        $this->assertSame('Body text', $crawler->filter('main > .wrapper > div > div > p')->text());
        $this->assertEmpty($crawler->filter('.view-selector'));
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            $this->fixture->articleRequest('00001'),
            $this->fixture->articleVorResponse('a-vor')
        );

        return '/content/1/e00001';
    }
}
