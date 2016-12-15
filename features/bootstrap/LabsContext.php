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

        $experiments = array_map(function (int $number) {
            return $this->faker->labsExperimentV1($number);
        }, range($number, 1));

        foreach (array_chunk($experiments, 6) as $i => $experimentsChunk) {
            $page = $i + 1;

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
                        'items' => array_map(function (array $experiment) {
                            unset($experiment['image']['banner']);
                            unset($experiment['content']);

                            return $experiment;
                        }, $experimentsChunk),
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
        $this->assertSession()
            ->elementsCount('css', '.grid-listing-heading:contains("Experiments") + ol > li', $number);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfExperiments - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.grid-listing-heading:contains("Experiments") + ol > li:nth-child('.$nthChild.')',
                'Experiment: '.str_pad($expectedNumber, 3, 0, STR_PAD_LEFT)
            );
        }
    }
}
