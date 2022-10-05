<?php

use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class HomepageContext extends Context
{
    private $numberOfArticles;
    private $numberOfMagazineArticles;
    private $alreadySubscribed;

    /**
     * @BeforeScenario
     */
    public function reset()
    {
        $this->numberOfArticles = null;
        $this->numberOfMagazineArticles = null;
        $this->alreadySubscribed = false;
    }

    /**
     * @Given /^(\d+) articles have been published$/
     */
    public function articlesHaveBeenPublished(int $number)
    {
        $this->numberOfArticles = $number;

        $articles = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
            $i = str_pad($i, 5, '0', STR_PAD_LEFT);
            $articles[] = [
                'status' => 'poa',
                'stage' => 'published',
                'id' => "$i",
                'version' => 1,
                'type' => 'research-article',
                'doi' => '10.7554/eLife.'.$i,
                'title' => 'Article '.$i.' title',
                'published' => $today->format(ApiSdk::DATE_FORMAT),
                'versionDate' => $today->format(ApiSdk::DATE_FORMAT),
                'statusDate' => $today->format(ApiSdk::DATE_FORMAT),
                'volume' => 5,
                'elocationId' => 'e'.$i,
                'copyright' => [
                    'license' => 'CC-BY-4.0',
                    'holder' => 'Author et al.',
                    'statement' => 'Creative Commons Attribution License.',
                ],
                'authorLine' => 'Foo Bar',
            ];
        }

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-communication&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=2'],
                json_encode([
                    'total' => $number,
                    'items' => [$articles[0]],
                    'subjects' => [
                        [
                            'id' => 'subject',
                            'name' => 'Some subject',
                            'results' => 0,
                        ],
                    ],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => $this->numberOfArticles,
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
                    ],
                ])
            )
        );

        foreach (array_chunk($articles, $chunk = 10) as $i => $articleChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/search?for=&page=$page&per-page=$chunk&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-communication&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default",
                    ['Accept' => 'application/vnd.elife.search+json; version=2']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.search+json; version=2'],
                    json_encode([
                        'total' => $number,
                        'items' => $articleChunk,
                        'subjects' => [
                            [
                                'id' => 'subject',
                                'name' => 'Some subject',
                                'results' => 0,
                            ],
                        ],
                        'types' => [
                            'correction' => 0,
                            'editorial' => 0,
                            'feature' => 0,
                            'insight' => 0,
                            'research-advance' => 0,
                            'research-article' => $this->numberOfArticles,
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
                        ],
                    ])
                )
            );
        }
    }

    /**
     * @Given /^the Magazine has (\d+) items$/
     */
    public function theMagazineHasItems(int $number)
    {
        $this->numberOfMagazineArticles = $number;

        $articles = [];

        $today = (new DateTimeImmutable('-1 day'))->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
            $articles[] = [
                'type' => 'podcast-episode',
                'number' => $i,
                'title' => 'Podcast episode '.$i.' title',
                'published' => $today->format(ApiSdk::DATE_FORMAT),
                'image' => [
                    'thumbnail' => [
                        'uri' => 'https://www.example.com/iiif/iden%2Ftifer',
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
                'sources' => [
                    [
                        'mediaType' => 'audio/mpeg',
                        'uri' => $this->locatePath('/tests/blank.mp3'),
                    ],
                ],
            ];
        }

        foreach (array_chunk($articles, 7) as $i => $articleChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/search?for=&page=$page&per-page=7&sort=date&order=desc&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&type[]=interview&type[]=podcast-episode&use-date=default",
                    ['Accept' => 'application/vnd.elife.search+json; version=2']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.search+json; version=2'],
                    json_encode([
                        'total' => $number,
                        'items' => $articleChunk,
                        'subjects' => [
                            [
                                'id' => 'subject',
                                'name' => 'Some subject',
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
                            'podcast-episode' => $this->numberOfMagazineArticles,
                        ],
                    ])
                )
            );
        }
    }

    /**
     * @Given /^there is a collection called \'([^\']*)\'$/
     */
    public function thereIsACollectionCalled(string $name)
    {
        // Do nothin.
    }

    /**
     * @Given /^there is a cover linking to the \'([^\']*)\' collection$/
     */
    public function thereIsACoverLinkingToTheCollection(string $collectionName)
    {
        $id = $this->createId($collectionName);

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/covers/current',
                ['Accept' => 'application/vnd.elife.cover-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.cover-list+json; version=1'],
                json_encode([
                    'total' => 1,
                    'items' => [
                        [
                            'title' => $collectionName,
                            'image' => [
                                'uri' => 'https://www.example.com/iiif/iden%2Ftifier',
                                'alt' => '',
                                'source' => [
                                    'mediaType' => 'image/jpeg',
                                    'uri' => 'https://www.example.com/image.jpg',
                                    'filename' => 'image.jpg',
                                ],
                                'size' => [
                                    'width' => 1800,
                                    'height' => 1600,
                                ],
                            ],
                            'item' => [
                                'type' => 'collection',
                                'id' => $id,
                                'title' => $collectionName,
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
                                        'uri' => 'https://www.example.com/iiif/thumb%2Fnail',
                                        'alt' => '',
                                        'source' => [
                                            'mediaType' => 'image/jpeg',
                                            'uri' => 'https://www.example.com/thumbnail.jpg',
                                            'filename' => 'thumbnail.jpg',
                                        ],
                                        'size' => [
                                            'width' => 1800,
                                            'height' => 1600,
                                        ],
                                    ],
                                ],
                                'selectedCurator' => [
                                    'id' => '1',
                                    'type' => [
                                        'id' => 'senior-editor',
                                        'label' => 'Senior editor',
                                    ],
                                    'name' => [
                                        'preferred' => 'Person 1',
                                        'index' => '1, Person',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );
    }

    /**
     * @Given /^there is a cover linking to the \'([^\']*)\' collection with a custom title and image$/
     */
    public function thereIsACoverLinkingToTheCollectionWithACustomTitleAndImage(string $collectionName)
    {
        $id = $this->createId($collectionName);

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/covers/current',
                ['Accept' => 'application/vnd.elife.cover-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.cover-list+json; version=1'],
                json_encode([
                    'total' => 1,
                    'items' => [
                        [
                            'title' => 'Cover',
                            'image' => [
                                'uri' => 'https://www.example.com/iiif/iden%2Ftifier',
                                'alt' => '',
                                'source' => [
                                    'mediaType' => 'image/png',
                                    'uri' => 'https://www.example.com/image.png',
                                    'filename' => 'image.png',
                                ],
                                'size' => [
                                    'width' => 1800,
                                    'height' => 1600,
                                ],
                            ],
                            'item' => [
                                'type' => 'collection',
                                'id' => $id,
                                'published' => $today->format(ApiSdk::DATE_FORMAT),
                                'title' => $collectionName,
                                'selectedCurator' => [
                                    'id' => '1',
                                    'type' => [
                                        'id' => 'senior-editor',
                                        'label' => 'Senior editor',
                                    ],
                                    'name' => [
                                        'preferred' => 'Person 1',
                                        'index' => '1, Person',
                                    ],
                                ],
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
                            ],
                        ],
                    ],
                ])
            )
        );
    }

    /**
     * @Given /^I am already subscribed$/
     */
    public function iAmAlreadySubscribed()
    {
        $this->alreadySubscribed = true;
    }

    /**
     * @Given /^I am on the homepage$/
     * @When /^I go to the homepage$/
     */
    public function iGoToTheHomepage()
    {
        if (null === $this->numberOfArticles) {
            $this->mockApiResponse(
                new Request(
                    'GET',
                    'http://api.elifesciences.org/search?for=&page=1&per-page=10&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-communication&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default',
                    ['Accept' => 'application/vnd.elife.search+json; version=2']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.search+json; version=2'],
                    json_encode([
                        'total' => 0,
                        'items' => [],
                        'subjects' => [],
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
                        ],
                    ])
                )
            );
        }

        $this->visitPath('/');
    }

    /**
     * @When /^I load more articles$/
     */
    public function iLoadMoreArticles()
    {
        $this->getSession()->getPage()->clickLink('Load more');
    }

    /**
     * @When /^I fill in the sign\-up form$/
     */
    public function iFillInTheSignUpForm()
    {
        $form = $this->assertSession()->elementExists('css', '#email_cta');
        $form->fillField('Email', 'foo@example.com');

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://crm.elifesciences.org/crm/civicrm/profile/create?reset=1&gid=18'
            ),
            new Response(
                200,
                ['Content-Type' => 'text/html'],
                '<html>
<body>
<form action="/crm/civicrm/profile/create" method="post">
<input type="text" name="email-3">
<input type="submit" value="Save">
</form>
</body>
</html>'
            )
        );

        if ($this->alreadySubscribed) {
            $this->mockApiResponse(
                new Request(
                    'POST',
                    'http://crm.elifesciences.org/crm/civicrm/profile/create',
                    ['Content-Type' => 'application/x-www-form-urlencoded'],
                    'email-3=foo%40example.com'
                ),
                new Response(
                    200,
                    ['Content-Type' => 'text/html'],
                    '<html>
<body>
<span class="msg-text">Your information has been saved</span>
</body>
</html>'
                )
            );
        } else {
            $this->mockApiResponse(
                new Request(
                    'POST',
                    'http://crm.elifesciences.org/crm/civicrm/profile/create',
                    ['Content-Type' => 'application/x-www-form-urlencoded'],
                    'email-3=foo%40example.com'
                ),
                new Response(
                    200,
                    ['Content-Type' => 'text/html'],
                    '<html>
<body>
<span class="messages">Your subscription request has been submitted</span>
</body>
</html>'
                )
            );
        }

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=10&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-communication&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=2'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                    'subjects' => [],
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
                    ],
                ])
            )
        );

        $form->submit();
    }

    /**
     * @Then /^I should see the latest (\d+) articles in the 'Latest research' list$/
     */
    public function iShouldSeeTheLatestArticlesInTheLatestResearchList(int $number)
    {
        $this->spin(function () use ($number) {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest research") + .listing-list > .listing-list__item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);
                $expectedNumber = ($this->numberOfArticles - $nthChild + 1);

                $this->assertSession()->elementContains(
                    'css',
                    '.list-heading:contains("Latest research") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                    'Article '.str_pad($expectedNumber, 5, '0', STR_PAD_LEFT).' title'
                );
            }
        });
    }

    /**
     * @Then /^I should see the latest (\d+) Magazine items in the 'Magazine' list$/
     */
    public function iShouldSeeTheLatestMagazineItemsInTheMagazineList(int $number)
    {
        $this->assertSession()->elementsCount('css', '.list-heading:contains("Magazine") + .listing-list > .listing-list__item', $number + 1);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfMagazineArticles - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Magazine") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                'Podcast episode '.$expectedNumber.' title'
            );
        }

        $this->assertSession()->elementContains(
            'css',
            '.list-heading:contains("Magazine") + .listing-list > .listing-list__item:nth-child('.($number + 1).')',
            'See more Magazine articles'
        );
    }

    /**
     * @Then /^I should see the \'([^\']*)\' cover in the carousel$/
     */
    public function iShouldSeeTheCoverInTheCarousel(string $name)
    {
        $this->spin(function () use ($name) {
            $this->assertSession()->elementAttributeContains('css', '.carousel-item__title_link', 'href', $this->createId($name));
        });
    }

    /**
     * @Then /^I should see the title and image from the \'([^\']*)\' collection used in the \'([^\']*)\' cover$/
     */
    public function iShouldSeeTheTitleAndImageFromTheCollectionUsedInTheCover(string $collectionName, string $coverName)
    {
        $this->spin(function () {
            $this->assertSession()->elementAttributeContains(
                'css',
                '.carousel-item__image',
                'src',
                'https://www.example.com/iiif/iden%2Ftifier/0,529,1800,543/1114,336/0/default.jpg'
            );
        });
    }

    /**
     * @Then /^I should see the custom title and image used in the \'([^\']*)\' cover$/
     */
    public function iShouldSeeTheCustomTitleAndImageUsedInTheCover($arg1)
    {
        $this->spin(function () {
            $this->assertSession()->elementAttributeContains(
                'css',
                '.carousel-item__image',
                'src',
                'https://www.example.com/iiif/iden%2Ftifier/0,529,1800,543/1114,336/0/default.png'
            );
        });
    }

    /**
     * @Then /^I should be prompted to check my email$/
     */
    public function iShouldBePromptedToCheckMyEmail()
    {
        $this->assertSession()
            ->elementContains('css', '.info-bar--success', 'Almost finished! Click the link in the email we just sent you to confirm your subscription.');
    }

    /**
     * @Then /^I should be reminded that I am already subscribed$/
     */
    public function iShouldBeRemindedThatIAmAlreadySubscribed()
    {
        $this->assertSession()
            ->elementContains('css', '.info-bar--success', 'You are already subscribed!');
    }

    private function createId(string $name) : string
    {
        return md5($name);
    }
}
