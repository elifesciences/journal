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
                [
                    'Accept' => [
                        'application/vnd.elife.article-poa+json; version=1',
                        'application/vnd.elife.article-vor+json; version=1',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=1'],
                json_encode([
                    'status' => 'vor',
                    'id' => '00001',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.00001',
                    'title' => 'Article title',
                    'published' => '2010-01-01T00:00:00+00:00',
                    'statusDate' => '2010-01-01T00:00:00+00:00',
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
    }

    /**
     * @When /^I go the research article page$/
     */
    public function iGoTheResearchArticlePage()
    {
        $this->visitPath('/content/1/e00001');
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
