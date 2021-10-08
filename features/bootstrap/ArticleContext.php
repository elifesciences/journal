<?php

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class ArticleContext extends Context
{
    /**
     * @Given /^there is a research article VoR$/
     */
    public function thereIsAResearchArticleVor()
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=5']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=5'],
                json_encode([
                    'status' => 'vor',
                    'stage' => 'published',
                    'id' => '00001',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.00001',
                    'title' => 'Article title',
                    'published' => '2010-01-01T00:00:00Z',
                    'versionDate' => '2010-01-01T00:00:00Z',
                    'statusDate' => '2010-01-01T00:00:00Z',
                    'volume' => 1,
                    'elocationId' => 'e00001',
                    'copyright' => [
                        'license' => 'CC-BY-4.0',
                        'holder' => 'Author One',
                        'statement' => 'Copyright statement.',
                    ],
                    'authorLine' => 'Author One',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'Author One',
                                'index' => 'Author One',
                            ],
                        ],
                    ],
                    'abstract' => [
                        'doi' => '10.7554/eLife.00001.001',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Abstract text',
                            ],
                        ],
                    ],
                    'digest' => [
                        'doi' => '10.7554/eLife.00001.002',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Digest text',
                            ],
                        ],
                    ],
                    'body' => [
                        [
                            'type' => 'section',
                            'id' => 's-1',
                            'title' => 'Introduction',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Introduction text',
                                ],
                            ],
                        ],
                        [
                            'type' => 'section',
                            'id' => 's-2',
                            'title' => 'Results',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Results text',
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001/versions',
                [
                    'Accept' => [
                        'application/vnd.elife.article-history+json; version=2',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=2'],
                json_encode([
                    'versions' => [
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '00001',
                            'version' => 1,
                            'type' => 'research-article',
                            'doi' => '10.7554/eLife.00001',
                            'title' => 'Article title',
                            'published' => '2010-01-01T00:00:00Z',
                            'versionDate' => '2010-01-01T00:00:00Z',
                            'statusDate' => '2010-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e00001',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author One',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Author One',
                        ],
                    ],
                ])
            )
        );
    }

    /**
     * @When /^I go the research article page$/
     */
    public function iGoTheResearchArticlePage()
    {
        $this->visitPath('/articles/00001');
    }

    /**
     * @Then /^the "([^"]*)" section should be (open|closed)$/
     */
    public function theSectionShouldBe(string $section, string $state)
    {
        $this->assertSession()->elementExists('css', '.article-section:contains("'.$section.'")');

        switch ($state) {
            case 'open':
                $this->assertSession()
                    ->elementAttributeNotContains('css',
                        '.article-section:contains("'.$section.'") .article-section__body', 'class',
                        'visuallyhidden');
                break;
            case 'closed':
                $this->assertSession()
                    ->elementAttributeContains('css',
                        '.article-section:contains("'.$section.'") .article-section__body', 'class',
                        'visuallyhidden');
                break;
        }
    }
}
