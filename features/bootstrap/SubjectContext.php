<?php

use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class SubjectContext extends Context
{
    private $numberOfSubjects;
    private $numberOfArticles;

    /**
     * @Given /^there are (\d+) subjects$/
     */
    public function thereAreSubjects(int $number)
    {
        $this->numberOfSubjects = $number;

        $subjects = [];

        for ($i = $number; $i > 0; --$i) {
            $subjects[] = [
                'id' => 'subject'.$i,
                'name' => 'Subject '.$i.' name',
                'impactStatement' => 'Subject '.$i.' impact statement.',
                'image' => [
                    'banner' => [
                        'alt' => '',
                        'sizes' => [
                            '2:1' => [
                                900 => 'https://placehold.it/900x450',
                                1800 => 'https://placehold.it/1800x900',
                            ],
                        ],
                    ],
                    'thumbnail' => [
                        'alt' => '',
                        'sizes' => [
                            '16:9' => [
                                250 => 'https://placehold.it/250x141',
                                500 => 'https://placehold.it/500x281',
                            ],
                            '1:1' => [
                                70 => 'https://placehold.it/70x70',
                                140 => 'https://placehold.it/140x140',
                            ],
                        ],
                    ],
                ],
            ];
        }

        foreach (array_chunk($subjects, 100) as $i => $subjectsChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/subjects?page=$page&per-page=100&order=asc",
                    ['Accept' => 'application/vnd.elife.subject-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.subject-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => $subjectsChunk,
                    ])
                )
            );
        }
    }

    /**
     * @Given /^there are (\d+) articles with the MSA \'([^\']*)\'$/
     */
    public function thereAreArticlesWithTheMSA(int $number, string $subject)
    {
        $this->numberOfArticles = $number;

        $articles = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        $subjectId = $this->createSubjectId($subject);

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects/'.$subjectId,
                [
                    'Accept' => 'application/vnd.elife.subject+json; version=1',
                ]
            ),
            new Response(
                200,
                [
                    'Content-Type' => 'application/vnd.elife.subject+json; version=1',
                ],
                json_encode([
                    'id' => $subjectId,
                    'name' => $subject,
                    'impactStatement' => $subject.' impact statement.',
                    'image' => [
                        'banner' => [
                            'alt' => '',
                            'sizes' => [
                                '2:1' => [
                                    900 => 'https://placehold.it/900x450',
                                    1800 => 'https://placehold.it/1800x900',
                                ],
                            ],
                        ],
                        'thumbnail' => [
                            'alt' => '',
                            'sizes' => [
                                '16:9' => [
                                    250 => 'https://placehold.it/250x141',
                                    500 => 'https://placehold.it/500x281',
                                ],
                                '1:1' => [
                                    70 => 'https://placehold.it/70x70',
                                    140 => 'https://placehold.it/140x140',
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );

        for ($i = $number; $i > 0; --$i) {
            $articles[] = [
                'type' => 'collection',
                'id' => "$i",
                'title' => 'Collection '.$i.' title',
                'updated' => $today->format(ApiSdk::DATE_FORMAT),
                'image' => [
                    'banner' => [
                        'alt' => '',
                        'sizes' => [
                            '2:1' => [
                                900 => 'https://placehold.it/900x450',
                                1800 => 'https://placehold.it/1800x900',
                            ],
                        ],
                    ],
                    'thumbnail' => [
                        'alt' => '',
                        'sizes' => [
                            '16:9' => [
                                250 => 'https://placehold.it/250x141',
                                500 => 'https://placehold.it/500x281',
                            ],
                            '1:1' => [
                                70 => 'https://placehold.it/70x70',
                                140 => 'https://placehold.it/140x140',
                            ],
                        ],
                    ],
                ],
                'subjects' => [
                    [
                        'id' => $subjectId,
                        'name' => $subject,
                    ],
                ],
                'selectedCurator' => [
                    'id' => "$i",
                    'type' => 'senior-editor',
                    'name' => [
                        'preferred' => 'Person '.$i,
                        'index' => $i.', Person',
                    ],
                ],
                'curators' => [
                    [
                        'id' => "$i",
                        'type' => 'senior-editor',
                        'name' => [
                            'preferred' => 'Person '.$i,
                            'index' => $i.', Person',
                        ],
                    ],
                ],
                'content' => [
                    [
                        'type' => 'blog-article',
                        'id' => "$i",
                        'title' => 'Blog article '.$i.' title',
                        'published' => $today->format(ApiSdk::DATE_FORMAT),
                    ],
                ],
            ];
        }

        foreach (array_chunk($articles, 6) as $i => $articleChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/search?for=&page=$page&per-page=6&sort=date&order=desc&subject[]=$subjectId&type[]=research-article&type[]=research-advance&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study&type[]=editorial&type[]=insight&type[]=feature&type[]=collection",
                    ['Accept' => 'application/vnd.elife.search+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => array_map(function (array $collection) {
                            unset($collection['image']['banner']);
                            unset($collection['curators']);
                            unset($collection['content']);

                            return $collection;
                        }, $articleChunk),
                        'subjects' => [
                            [
                                'id' => $subjectId,
                                'name' => $subject,
                                'results' => count($articles),
                            ],
                        ],
                        'types' => [
                            'correction' => 0,
                            'editorial' => 0,
                            'feature' => 0,
                            'insight' => 0,
                            'research-advance' => 0,
                            'research-article' => 0,
                            'research-exchange' => 0,
                            'retraction' => 0,
                            'registered-report' => 0,
                            'replication-study' => 0,
                            'short-report' => 0,
                            'tools-resources' => 0,
                            'blog-article' => 0,
                            'collection' => $this->numberOfArticles,
                            'event' => 0,
                            'interview' => 0,
                            'labs-experiment' => 0,
                            'podcast-episode' => 0,
                        ],
                    ])
                )
            );
        }
    }

    /**
     * @When /^I go the Subjects page$/
     */
    public function iGoTheSubjectsPage()
    {
        $this->visitPath('/subjects');
    }

    /**
     * @When /^I go the MSA \'([^\']*)\' page$/
     */
    public function iGoTheMSAPage(string $subject)
    {
        $this->visitPath('/subjects/'.$this->createSubjectId($subject));
    }

    /**
     * @When /^I load more articles$/
     */
    public function iLoadMoreArticles()
    {
        $this->getSession()->getPage()->clickLink('More articles');
    }

    /**
     * @Then /^I should see the (\d+) subjects\.$/
     */
    public function iShouldSeeTheSubjects(int $number)
    {
        $this->assertSession()->elementsCount('css', 'ol.grid-listing > li', $number);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfSubjects - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                'ol.grid-listing > li:nth-child('.$nthChild.')',
                'Subject '.$expectedNumber.' name'
            );
        }
    }

    /**
     * @Then /^I should see the latest (\d+) items with the MSA \'([^\']*)\' in the 'Latest articles' list$/
     */
    public function iShouldSeeTheLatestItemsWithTheMSAInTheLatestArticlesList(int $number, string $subject)
    {
        $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest articles") + ol > li', $number);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfArticles - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Latest articles") + ol > li:nth-child('.$nthChild.')',
                'Collection '.$expectedNumber.' title'
            )
            ;
            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Latest articles") + ol > li:nth-child('.$nthChild.')',
                $subject
            )
            ;
        }
    }

    private function createSubjectId(string $subjectName) : string
    {
        return md5($subjectName);
    }
}
