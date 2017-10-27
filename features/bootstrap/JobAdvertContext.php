<?php

use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class JobAdvertContext extends Context
{
    private $numberOfJobAdverts;

    /**
     * @Given /^there are (\d+) open job adverts$/
     */
    public function thereAreOpenJobAdverts(int $number)
    {
        $this->numberOfJobAdverts = $number;

        $jobAdverts = [];

        for ($i = $number; $i > 0; --$i) {
            $closingDate = (new DateTimeImmutable())->setTime(0, 0, 0)->modify("+${i} day");
            $jobAdverts[] = [
                'id' => "$i",
                'title' => 'Job advert '.$i.' title',
                'impactStatement' => 'Job advert '.$i.' impact statement',
                'published' => '2010-01-01T00:00:00Z',
                'closingDate' => $closingDate->format(ApiSdk::DATE_FORMAT),
                'content' => [
                  [
                    'type' => 'paragraph',
                    'text' => 'Job advert '.$i.' content paragraph text.',
                  ],
                ],
            ];
        }

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/job-adverts?page=1&per-page=1&show=open&order=desc',
                ['Accept' => 'application/vnd.elife.job-advert-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.job-advert-list+json; version=1'],
                json_encode([
                    'total' => $number,
                    'items' => $jobAdverts,
                ])
            )
        );

        foreach (array_chunk($jobAdverts, $chunk = 10) as $i => $jobAdvertsChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/job-adverts?page=$page&per-page=$chunk&show=open&order=desc",
                    ['Accept' => 'application/vnd.elife.job-advert-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.job-advert-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => $jobAdvertsChunk,
                    ])
                )
            );

            foreach ($jobAdvertsChunk as $jobAdvert) {
                $this->mockApiResponse(
                    new Request(
                        'GET',
                        'http://api.elifesciences.org/job-adverts/'.$jobAdvert['id'],
                        ['Accept' => 'application/vnd.elife.job-advert+json; version=1']
                    ),
                    new Response(
                        200,
                        ['Content-Type' => 'application/vnd.elife.job-advert+json; version=1'],
                        json_encode($jobAdvert)
                    )
                );
            }
        }
    }

    /**
     * @When /^I go to the job adverts page$/
     */
    public function iGoTheJobAdvertsPage()
    {
        $this->visitPath('/job-adverts');
    }

    /**
     * @When /^I load more job adverts$/
     */
    public function iLoadMoreJobAdverts()
    {
        $this->getSession()->getPage()->clickLink('Load more');
    }

    /**
     * @Then /^I should see the (\d+) job adverts with the nearest closing dates in the 'Latest' list$/
     */
    public function iShouldSeeTheJobAdvertsWithTheNearestClosingDatesInTheLatestList(int $number)
    {
        $this->spin(function () use ($number) {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest") + .listing-list > .listing-list__item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);
                $expectedNumber = ($this->numberOfJobAdverts - $nthChild + 1);

                $this->assertSession()->elementContains(
                    'css',
                    '.list-heading:contains("Latest") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                    'Job advert '.$expectedNumber.' title'
                );
            }
        });
    }

    /**
     * @When /^I go to a page for a job advert that has a closing date in the past$/
     */
    public function iGoToAPageForAJobAdvertThatHasAClosingDateInThePast()
    {
        $expiredJobAdvert = [
        'id' => '1',
        'title' => 'Expired job advert title',
        'impactStatement' => 'Expired job advert impact statement',
        'published' => '2010-01-01T00:00:00Z',
        'closingDate' => ((new DateTimeImmutable())->setTime(0, 0, 0))->modify('-1 day'),
        'content' => [
          [
            'type' => 'paragraph',
            'text' => 'Expired job advert content paragraph text.',
          ],
        ],
      ];

        $this->mockApiResponse(
        new Request(
          'GET',
          'http://api.elifesciences.org/job-adverts/1',
          ['Accept' => 'application/vnd.elife.job-advert+json; version=1']
        ),
        new Response(
          200,
          ['Content-Type' => 'application/vnd.elife.job-advert+json; version=1'],
          json_encode($expiredJobAdvert)
        )
      );
    }

  /**
   * @Given /^there is a closed job advert$/
   */
  public function thereIsAClosedJobAdvert()
  {
      $historicalClosingDate = (new DateTimeImmutable())->setTime(0, 0, 0)->modify('-1 day');
      $jobAdvert = [
      'id' => '1',
      'title' => 'Closed job advert title',
      'impactStatement' => 'Closed job advert impact statement',
      'published' => '2010-01-01T00:00:00Z',
      'closingDate' => $historicalClosingDate->format(ApiSdk::DATE_FORMAT),
      'content' => [
        [
          'type' => 'paragraph',
          'text' => 'Closed job advert content paragraph text.',
        ],
      ],
    ];

      $this->mockApiResponse(
      new Request(
        'GET',
        'http://api.elifesciences.org/job-adverts/1',
        ['Accept' => 'application/vnd.elife.job-advert+json; version=1']
      ),
      new Response(
        200,
        ['Content-Type' => 'application/vnd.elife.job-advert+json; version=1'],
        json_encode($jobAdvert)
      )
    );
  }

  /**
   * @When /^I go to the closed job advert$/
   */
  public function iGoToTheClosedJobAdvert()
  {
      $this->visitPath('/job-adverts/1/closed-job-advert-title');
  }

  /**
   * @Then /^I should see text "([^"]*)"$/
   */
  public function iShouldSeeText(string $expectedText)
  {
      $this->assertSession()->pageTextContains($expectedText);
  }

  /**
   * @Given /^I should not see text "([^"]*)"$/
   */
  public function iShouldNotSeeText(string $forbiddenText)
  {
      $this->assertSession()->pageTextNotContains($forbiddenText);
  }
}
