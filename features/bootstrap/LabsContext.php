<?php

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class LabsContext extends Context
{
    private $numberOfExperiments;

    /**
     * @Given /^there are (\d+) Labs experiments$/
     */
    public function thereAreLabsExperiments(int $number)
    {
        $this->numberOfExperiments = $number;

        $experiments = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
            $experiments[] = [
                'number' => $i,
                'title' => 'Experiment '.$i.' title',
                'published' => $today->format(DATE_RFC3339),
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
                'impactStatement' => 'Experiment '.$i.' impact statement',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Experiment '.$i.' text.',
                    ],
                ],
            ];
        }

        foreach (array_chunk($experiments, 6) as $i => $experimentsChunk) {
            $page = $i + 1;

            unset($experimentsChunk['content']);

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/labs-experiments?page=$page&per-page=6&order=desc",
                    ['Accept' => 'application/vnd.elife.labs-experiment-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.labs-experiment-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => $experimentsChunk,
                    ])
                )
            );

            foreach ($experimentsChunk as $experiment) {
                $this->mockApiResponse(
                    new Request(
                        'GET',
                        'http://api.elifesciences.org/labs-experiments/'.$experiment['number'],
                        ['Accept' => 'application/vnd.elife.labs-experiment+json; version=1']
                    ),
                    new Response(
                        200,
                        ['Content-Type' => 'application/vnd.elife.labs-experiment+json; version=1'],
                        json_encode($experiment)
                    )
                );
            }
        }
    }

    /**
     * @Given /^I am on a Labs experiment page$/
     */
    public function iAmOnALabsExperimentPage()
    {
        $this->visitPath('/labs/experiment1');
    }

    /**
     * @When /^I go the Labs page$/
     */
    public function iGoTheLabsPage()
    {
        $this->visitPath('/labs');
    }

    /**
     * @Then /^I should see the latest (\d+) Labs experiments in the 'Experiments' list$/
     */
    public function iShouldSeeTheLatestLabsExperimentsInTheExperimentsList(int $number)
    {
        $this->assertSession()->elementsCount('css', '.list-heading:contains("Experiments") + ul > li', $number);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfExperiments - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Experiments") + ul > li:nth-child('.$nthChild.')',
                'Experiment '.$expectedNumber.' title'
            );
        }
    }
}
