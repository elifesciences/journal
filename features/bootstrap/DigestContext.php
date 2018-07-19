<?php

use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class DigestContext extends Context
{
    private $numberOfDigests;

    /**
     * @BeforeScenario
     */
    final public function setFeatureFlagCookie()
    {
        putenv('FEATURE_DIGEST_CHANNEL=true');
        $this->visitPath('/?FEATURE_DIGEST_CHANNEL=true');
    }

    /**
     * @Given /^there are (\d+) digests$/
     */
    public function thereAreDigests(int $number)
    {
        $this->numberOfDigests = $number;

        $digests = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
            $digests[] = [
                'id' => "{$i}",
                'title' => "Digest {$i} title",
                'published' => $today->format(ApiSdk::DATE_FORMAT),
                'image' => [
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
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => "Digest {$i} content.",
                    ],
                ],
                'relatedContent' => [
                    [
                        'type' => 'research-article',
                        'status' => 'vor',
                        'stage' => 'published',
                        'id' => '12345',
                        'version' => 1,
                        'doi' => '10.7554/eLife.12345',
                        'title' => 'Article 12345',
                        'published' => '2010-01-01T00:00:00Z',
                        'versionDate' => '2010-01-01T00:00:00Z',
                        'statusDate' => '2010-01-01T00:00:00Z',
                        'volume' => 5,
                        'elocationId' => 'e12345',
                        'authorLine' => 'Foo Bar',
                    ],
                ],
            ];
        }

        foreach (array_chunk($digests, $chunk = 8) as $i => $digestsChunk) {
            $page = $i + 1;

            unset($digestsChunk['content']);

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/digests?page={$page}&per-page={$chunk}&order=desc",
                    ['Accept' => 'application/vnd.elife.digest-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.digest-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => $digestsChunk,
                    ])
                )
            );

            foreach ($digestsChunk as $digest) {
                $this->mockApiResponse(
                    new Request(
                        'GET',
                        "http://api.elifesciences.org/digests/{$digest['id']}",
                        ['Accept' => 'application/vnd.elife.digest+json; version=1']
                    ),
                    new Response(
                        200,
                        ['Content-Type' => 'application/vnd.elife.digest+json; version=1'],
                        json_encode($digest)
                    )
                );
            }
        }
    }

    /**
     * @When /^I go the Science Digests page$/
     */
    public function iGoTheScienceDigestsPage()
    {
        $this->visitPath('/digests');
    }

    /**
     * @When /^I load more digests$/
     */
    public function iLoadMoreDigests()
    {
        $this->getSession()->getPage()->clickLink('Load more');
    }

    /**
     * @Then /^I should see the latest (\d+) digests in the 'Latest' list$/
     */
    public function iShouldSeeTheLatestDigestsInTheLatestList(int $number)
    {
        $this->spin(function () use ($number) {
            $this->assertSession()
                ->elementsCount('css', '.list-heading:contains("Latest") + .grid-listing > .grid-listing-item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);
                $expectedNumber = ($this->numberOfDigests - $nthChild + 1);

                $this->assertSession()->elementContains(
                    'css',
                    ".list-heading:contains('Latest') + .grid-listing > .grid-listing-item:nth-child({$nthChild})",
                    "Digest {$expectedNumber} title"
                );
            }
        });
    }
}
