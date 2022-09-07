<?php

use Behat\Mink\Exception\ElementHtmlException;
use eLife\ApiSdk\ApiSdk;
use eLife\Journal\Helper\Callback;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class SubjectContext extends Context
{
    private $emptyArticles;
    private $numberOfSubjects;
    private $numberOfArticles;
    private $numberOfHighlightedArticles;
    private $numberOfSeniorEditors;

    /**
     * @BeforeScenario
     */
    public function resetEmptyArticles()
    {
        $this->emptyArticles = true;
    }

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
                        'uri' => "https://www.example.com/iiif/banner%2F$i",
                        'alt' => '',
                        'source' => [
                            'mediaType' => 'image/jpeg',
                            'uri' => "https://www.example.com/banner$i.jpg",
                            'filename' => "banner$i.jpg",
                        ],
                        'size' => [
                            'width' => 1800,
                            'height' => 1600,
                        ],
                    ],
                    'thumbnail' => [
                        'uri' => "https://www.example.com/iiif/thumbnail%2F$i",
                        'alt' => '',
                        'source' => [
                            'mediaType' => 'image/jpeg',
                            'uri' => "https://www.example.com/thumbnail$i.jpg",
                            'filename' => "thumbnail$i.jpg",
                        ],
                        'size' => [
                            'width' => 800,
                            'height' => 600,
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

    private function mockSubject(string $subject)
    {
        $subjectId = $this->createSubjectId($subject);

        static::mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/subjects/$subjectId",
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
                    'impactStatement' => "$subject impact statement.",
                    'image' => [
                        'banner' => [
                            'uri' => 'https://www.example.com/iiif/ban%2Fner',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/banner.jpg',
                                'filename' => 'banner.jpg',
                            ],
                            'size' => [
                                'width' => 1800,
                                'height' => 1600,
                            ],
                        ],
                        'thumbnail' => [
                            'uri' => 'https://www.example.com/iiif/thumb%2Fnail',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/thumbnail.jpg',
                                'filename' => 'thumbnail.jpg',
                            ],
                            'size' => [
                                'width' => 800,
                                'height' => 600,
                            ],
                        ],
                    ],
                ])
            )
        );
    }

    /**
     * @Given /^there are (\d+) articles with the MSA \'([^\']*)\'$/
     */
    public function thereAreArticlesWithTheMSA(int $number, string $subject)
    {
        $this->emptyArticles = false;
        $this->numberOfArticles = $number;

        $articles = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        $subjectId = $this->createSubjectId($subject);

        $this->mockSubject($subject);

        for ($i = $number; $i > 0; --$i) {
            $articles[] = [
                'type' => 'collection',
                'id' => "$i",
                'title' => 'Collection '.$i.' title',
                'published' => $today->format(ApiSdk::DATE_FORMAT),
                'image' => [
                    'banner' => [
                        'uri' => 'https://www.example.com/iiif/ban%2Fner',
                        'alt' => '',
                        'source' => [
                            'mediaType' => 'image/jpeg',
                            'uri' => 'https://www.example.com/banner.jpg',
                            'filename' => 'banner.jpg',
                        ],
                        'size' => [
                            'width' => 1800,
                            'height' => 1600,
                        ],
                    ],
                    'thumbnail' => [
                        'uri' => 'https://www.example.com/iiif/iden%2Ftifier',
                        'alt' => '',
                        'source' => [
                            'mediaType' => 'image/jpeg',
                            'uri' => 'https://www.example.com/image.jpg',
                            'filename' => 'image.jpg',
                        ],
                        'size' => [
                            'width' => 800,
                            'height' => 600,
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
                    'type' => [
                        'id' => 'senior-editor',
                        'label' => 'Senior editor',
                    ],
                    'name' => [
                        'preferred' => 'Person '.$i,
                        'index' => $i.', Person',
                    ],
                ],
                'curators' => [
                    [
                        'id' => "$i",
                        'type' => [
                            'id' => 'senior-editor',
                            'label' => 'Senior editor',
                        ],
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

        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&subject[]=$subjectId&type[]=research-article&type[]=research-communication&type[]=research-advance&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&use-date=default",
                ['Accept' => 'application/vnd.elife.search+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=2'],
                json_encode([
                    'total' => $number,
                    'items' => array_map(function (array $collection) {
                        unset($collection['image']['banner']);
                        unset($collection['curators']);
                        unset($collection['content']);

                        return $collection;
                    }, [$articles[0]]),
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
                        'research-communication' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'review-article' => 0,
                        'scientific-correspondence' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => $this->numberOfArticles,
                        'interview' => 0,
                        'labs-post' => 0,
                        'podcast-episode' => 0,
                        'reviewed-preprint' => 0,
                    ],
                ])
            )
        );

        foreach (array_chunk($articles, $chunk = 10) as $i => $articleChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/search?for=&page=$page&per-page=$chunk&sort=date&order=desc&subject[]=$subjectId&type[]=research-article&type[]=research-communication&type[]=research-advance&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&use-date=default",
                    ['Accept' => 'application/vnd.elife.search+json; version=2']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.search+json; version=2'],
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
                            'research-communication' => 0,
                            'retraction' => 0,
                            'registered-report' => 0,
                            'replication-study' => 0,
                            'review-article' => 0,
                            'scientific-correspondence' => 0,
                            'short-report' => 0,
                            'tools-resources' => 0,
                            'blog-article' => 0,
                            'collection' => $this->numberOfArticles,
                            'interview' => 0,
                            'labs-post' => 0,
                            'podcast-episode' => 0,
                            'reviewed-preprint' => 0,
                        ],
                    ])
                )
            );
        }
    }

    /**
     * @Given /^there are (\d+) highlighted articles with the MSA \'([^\']*)\'$/
     */
    public function thereAreHighlightedArticlesWithTheMSA(int $number, string $subject)
    {
        $this->numberOfHighlightedArticles = $number;

        $articles = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        $subjectId = $this->createSubjectId($subject);

        $this->mockSubject($subject);

        for ($i = $number; $i > 0; --$i) {
            $articles[] = [
                'title' => "Collection $i highlight title",
                'item' => [
                    'type' => 'collection',
                    'id' => "$i",
                    'title' => 'Collection '.$i.' title',
                    'published' => $today->format(ApiSdk::DATE_FORMAT),
                    'image' => [
                        'thumbnail' => [
                            'uri' => 'https://www.example.com/iiif/iden%2Ftifier',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/image.jpg',
                                'filename' => 'image.jpg',
                            ],
                            'size' => [
                                'width' => 800,
                                'height' => 600,
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
                        'type' => [
                            'id' => 'senior-editor',
                            'label' => 'Senior editor',
                        ],
                        'name' => [
                            'preferred' => 'Person '.$i,
                            'index' => $i.', Person',
                        ],
                    ],
                ],
            ];
        }

        foreach (array_chunk($articles, 3) as $i => $articlesChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/highlights/$subjectId?page=$page&per-page=3&order=desc",
                    ['Accept' => 'application/vnd.elife.highlight-list+json; version=3']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.highlight-list+json; version=3'],
                    json_encode([
                        'total' => $number,
                        'items' => $articlesChunk,
                    ])
                )
            );
        }
    }

    /**
     * @Given /^there are (\d+) senior editors for the MSA \'([^\']*)\'$/
     */
    public function thereAreSeniorEditorsForTheMSA(int $number, string $subject)
    {
        $this->numberOfSeniorEditors = $number;

        $seniorEditors = [];

        $subjectId = $this->createSubjectId($subject);

        $this->mockSubject($subject);

        for ($i = $number; $i > 0; --$i) {
            $seniorEditors[] = [
                'id' => "$i",
                'type' => [
                    'id' => 'senior-editor',
                    'label' => 'Senior editor',
                ],
                'name' => [
                    'preferred' => "Person $i",
                    'index' => "Person $i",
                ],
                'research' => [
                    'expertises' => [
                        [
                            'id' => $subjectId,
                            'name' => $subject,
                        ],
                    ],
                    'focuses' => [],
                ],
            ];
        }

        foreach (array_chunk($seniorEditors, 100) as $i => $seniorEditorChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/people?page=$page&per-page=100&order=asc&subject[]=$subjectId&type[]=leadership&type[]=senior-editor",
                    ['Accept' => 'application/vnd.elife.person-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => $seniorEditorChunk,
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
        $subjectId = $this->createSubjectId($subject);

        if ($this->emptyArticles) {
            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/search?for=&page=1&per-page=10&sort=date&order=desc&subject[]=$subjectId&type[]=reviewed-preprint&type[]=research-article&type[]=research-communication&type[]=research-advance&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&use-date=default",
                    ['Accept' => 'application/vnd.elife.search+json; version=2']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.search+json; version=2'],
                    json_encode([
                        'total' => 0,
                        'items' => [],
                        'subjects' => [
                            [
                                'id' => $subjectId,
                                'name' => $subject,
                                'results' => 0,
                            ],
                        ],
                        'types' => [
                            'correction' => 0,
                            'editorial' => 0,
                            'feature' => 0,
                            'insight' => 0,
                            'research-advance' => 0,
                            'research-article' => 0,
                            'research-communication' => 0,
                            'retraction' => 0,
                            'registered-report' => 0,
                            'replication-study' => 0,
                            'review-article' => 0,
                            'scientific-correspondence' => 0,
                            'short-report' => 0,
                            'tools-resources' => 0,
                            'blog-article' => 0,
                            'collection' => 0,
                            'interview' => 0,
                            'labs-post' => 0,
                            'podcast-episode' => 0,
                            'reviewed-preprint' => 0,
                        ],
                    ])
                )
            );
        }

        $this->visitPath("/subjects/$subjectId");
    }

    /**
     * @When /^I load more articles$/
     */
    public function iLoadMoreArticles()
    {
        $this->getSession()->getPage()->clickLink('Load more');
    }

    /**
     * @Then /^I should see the (\d+) subjects\.$/
     */
    public function iShouldSeeTheSubjects(int $number)
    {
        $this->assertSession()->elementsCount('css', '.grid-listing > .grid-listing-item', $number);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfSubjects - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.grid-listing > .grid-listing-item:nth-child('.$nthChild.')',
                'Subject '.$expectedNumber.' name'
            );
        }
    }

    /**
     * @Then /^I should see the latest (\d+) items with the MSA \'([^\']*)\' in the 'Latest articles' list$/
     */
    public function iShouldSeeTheLatestItemsWithTheMSAInTheLatestArticlesList(int $number, string $subject)
    {
        $this->spin(function () use ($number, $subject) {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest articles") + .listing-list > .listing-list__item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);
                $expectedNumber = ($this->numberOfArticles - $nthChild + 1);

                $this->assertSession()->elementContains(
                    'css',
                    '.list-heading:contains("Latest articles") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                    'Collection '.$expectedNumber.' title'
                );
                $this->assertSession()->elementContains(
                    'css',
                    '.list-heading:contains("Latest articles") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                    $subject
                );
            }
        });
    }

    private function createSubjectId(string $subjectName) : string
    {
        return md5($subjectName);
    }

    /**
     * @Given /^I should see the latest (\d+) highlighted articles with the MSA \'([^\']*)\' in the 'Highlights' list$/
     */
    public function iShouldSeeTheLatestHighlightedArticlesWithTheMSAInTheList(int $number, string $subject)
    {
        $this->assertSession()->elementsCount('css', '.list-heading:contains("Highlights") + .listing-list > .listing-list__item', $number);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfHighlightedArticles - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Highlights") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                "Collection $expectedNumber highlight title"
            );
            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Highlights") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                $subject
            );
        }
    }

    /**
     * @Then /^I should see (\d+) seniors editors for the MSA \'([^\']*)\' sorted by surname in the 'Senior editors' list$/
     * @Then /^I should see the (\d+) seniors editors for the MSA \'([^\']*)\' sorted by surname in the 'Senior editors' list$/
     */
    public function iShouldSeeSeniorsEditorsForTheMSASortedBySurnameInTheList(int $number, string $subject)
    {
        $list = $this->assertSession()->elementExists('css', '.list-heading:contains("Senior editors") + .listing-list');
        $this->assertSession()->elementsCount('css', '.list-heading:contains("Senior editors") + .listing-list > .listing-list__item .profile-snippet__name', $number);

        $names = array_map(Callback::method('getText'), $this->getSession()->getPage()->findAll(
            'css',
            '.list-heading:contains("Senior editors") + .listing-list > .listing-list__item .profile-snippet__name'
        ));

        $namesCheck = $names;
        sort($namesCheck);

        if ($names !== $namesCheck) {
            throw new ElementHtmlException('List is not sorted correctly: '.print_r($namesCheck, true), $this->getSession()->getDriver(), $list);
        }
    }
}
