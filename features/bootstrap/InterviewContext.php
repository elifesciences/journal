<?php

use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class InterviewContext extends Context
{
    private $numberOfInterviews;

    /**
     * @Given /^there are (\d+) interviews$/
     */
    public function thereAreInterviews(int $number)
    {
        $this->numberOfInterviews = $number;

        $interviews = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
            $interviews[] = [
                'id' => 'interview'.$i,
                'interviewee' => [
                    'name' => [
                        'preferred' => 'Interviewee '.$i,
                        'index' => 'Interviewee '.$i,
                    ],
                ],
                'title' => 'Interview '.$i.' title',
                'published' => $today->format(ApiSdk::DATE_FORMAT),
            ];
        }

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/interviews?page=1&per-page=1&order=desc',
                ['Accept' => 'application/vnd.elife.interview-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.interview-list+json; version=1'],
                json_encode([
                    'total' => $number,
                    'items' => [$interviews[0]],
                ])
            )
        );

        foreach (array_chunk($interviews, 6) as $i => $interviewChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/interviews?page=$page&per-page=6&order=desc",
                    ['Accept' => 'application/vnd.elife.interview-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.interview-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => $interviewChunk,
                    ])
                )
            );
        }
    }

    /**
     * @When /^I go to the interviews page$/
     */
    public function iGoToTheInterviewsPage()
    {
        $this->visitPath('/interviews');
    }

    /**
     * @When /^I load more interviews$/
     */
    public function iLoadMoreInterviews()
    {
        $this->getSession()->getPage()->clickLink('Load more');
    }

    /**
     * @Then /^I should see the latest (\d+) interviews in the 'Latest' list$/
     */
    public function iShouldSeeTheLatestInterviewsInTheLatestList(int $number)
    {
        $this->spin(function () use ($number) {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest")', 1);
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest") + .listing-list > .listing-list__item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);
                $expectedNumber = ($this->numberOfInterviews - $nthChild + 1);

                $this->assertSession()->elementContains(
                    'css',
                    '.list-heading:contains("Latest") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                    'Interview '.$expectedNumber.' title'
                );
            }
        });
    }
}
