<?php

use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class PressPackageContext extends Context
{
    private $numberOfPackages;

    /**
     * @Given /^there are (\d+) press packs$/
     */
    public function thereArePressPacks(int $number)
    {
        $this->numberOfPackages = $number;

        $packages = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
            $packages[] = [
                'id' => "press-package-$i",
                'title' => "Press package $i title",
                'published' => $today->format(ApiSdk::DATE_FORMAT),
            ];
        }

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/press-packages?page=1&per-page=1&order=desc',
                ['Accept' => 'application/vnd.elife.press-package-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.press-package-list+json; version=1'],
                json_encode([
                    'total' => $number,
                    'items' => [$packages[0]],
                ])
            )
        );

        foreach (array_chunk($packages, 6) as $i => $packageChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/press-packages?page=$page&per-page=6&order=desc",
                    ['Accept' => 'application/vnd.elife.press-package-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.press-package-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => $packageChunk,
                    ])
                )
            );
        }
    }

    /**
     * @When /^I go to the 'For the press' page$/
     */
    public function iGoToTheForThePressPage()
    {
        $this->visitPath('/for-the-press');
    }

    /**
     * @When /^I load more press packs$/
     */
    public function iLoadMorePressPacks()
    {
        $this->getSession()->getPage()->clickLink('Load more');
    }

    /**
     * @Then /^I should see the latest (\d+) press packs in the 'Latest' list$/
     */
    public function iShouldSeeTheLatestPressPacksInTheLatestList(int $number)
    {
        $this->spin(function () use ($number) {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest")', 1);
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest") + .listing-list > .listing-list__item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);
                $expectedNumber = ($this->numberOfPackages - $nthChild + 1);

                $this->assertSession()->elementContains(
                    'css',
                    '.list-heading:contains("Latest") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                    "Press package $expectedNumber title"
                );
            }
        });
    }
}
