<?php

use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class EventContext extends Context
{
    private $numberOfEvents;

    /**
     * @Given /^there are (\d+) upcoming events$/
     */
    public function thereAreUpcomingEvents(int $number)
    {
        $this->numberOfEvents = $number;

        $events = [];

        $starts = (new DateTimeImmutable())->setTime(0, 0, 0);
        $ends = $starts->modify('+1 day');

        for ($i = $number; $i > 0; --$i) {
            $events[] = [
                'id' => "$i",
                'title' => 'Event '.$i.' title',
                'starts' => $starts->format(ApiSdk::DATE_FORMAT),
                'ends' => $ends->format(ApiSdk::DATE_FORMAT),
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Event '.$i.' text.',
                    ],
                ],
            ];
        }

        foreach (array_chunk($events, 6) as $i => $eventsChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/events?page=$page&per-page=6&type=open&order=asc",
                    ['Accept' => 'application/vnd.elife.event-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.event-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => array_map(function (array $item) {
                            unset($item['content']);

                            return $item;
                        }, $eventsChunk),
                    ])
                )
            );

            foreach ($eventsChunk as $event) {
                $this->mockApiResponse(
                    new Request(
                        'GET',
                        'http://api.elifesciences.org/events/'.$event['id'],
                        ['Accept' => 'application/vnd.elife.event+json; version=1']
                    ),
                    new Response(
                        200,
                        ['Content-Type' => 'application/vnd.elife.event+json; version=1'],
                        json_encode($event)
                    )
                );
            }
        }
    }

    /**
     * @When /^I go to the events page$/
     */
    public function iGoTheEventsPage()
    {
        $this->visitPath('/events');
    }

    /**
     * @When /^I load more events$/
     */
    public function iLoadMoreEvents()
    {
        $this->getSession()->getPage()->clickLink('More events');
    }

    /**
     * @Then /^I should see the (\d+) earliest upcoming events in the 'Upcoming events' list$/
     */
    public function iShouldSeeTheEarliestUpcomingEventsInTheUpcomingEventsList(int $number)
    {
        $this->assertSession()->elementsCount('css', '.list-heading:contains("Upcoming events") + ol > li', $number);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfEvents - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Upcoming events") + ol > li:nth-child('.$nthChild.')',
                'Event '.$expectedNumber.' title'
            );
        }
    }
}
