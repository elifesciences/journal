<?php

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class SubjectContext extends Context
{
    private $numberOfArticles;

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
                        'alt' => '',
                        'sizes' => [
                            '2:1' => [
                                900 => 'https://placehold.it/900x450',
                                1800 => 'https://placehold.it/1800x900',
                            ],
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
                ])
            )
        );

        for ($i = $number; $i > 0; --$i) {
            $articles[] = [
                'type' => 'podcast-episode',
                'number' => $i,
                'title' => 'Podcast episode '.$i,
                'published' => $today->format(DATE_RFC3339),
                'image' => [
                    'alt' => '',
                    'sizes' => [
                        '2:1' => [
                            '900' => 'https://placehold.it/900x450',
                            '1800' => 'https://placehold.it/1800x900',
                        ],
                        '16:9' => [
                            '250' => 'https://placehold.it/250x141',
                            '500' => 'https://placehold.it/500x281',
                        ],
                        '1:1' => [
                            '70' => 'https://placehold.it/70x70',
                            '140' => 'https://placehold.it/140x140',
                        ],
                    ],
                ],
                'mp3' => 'https://www.example.com/episode'.$i.'.mp3',
                'subjects' => [$subjectId],
            ];
        }

        foreach (array_chunk($articles, 6) as $i => $articleChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/search?for=&page=$page&per-page=6&sort=date&order=desc&subject[]=$subjectId",
                    ['Accept' => 'application/vnd.elife.search+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => $articleChunk,
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
                            'collection' => 0,
                            'event' => 0,
                            'labs-experiment' => 0,
                            'podcast-episode' => $this->numberOfArticles,
                        ],
                    ])
                )
            );
        }
    }

    /**
     * @When /^I go the MSA \'([^\']*)\' page$/
     */
    public function iGoTheMSAPage(string $subject)
    {
        $this->visitPath('/subjects/'.$this->createSubjectId($subject));
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
                'Podcast episode '.$expectedNumber
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
