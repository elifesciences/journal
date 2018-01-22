<?php

use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class ProfileContext extends Context
{
    private $numberOfAnnotations;

    /**
     * @BeforeScenario
     */
    public function enableFeature()
    {
        $this->visitPath('/about?open-sesame');
    }

    /**
     * @Given /^([A-Za-z\s]+) has (\d+) annotations?$/
     */
    public function profileHasAnnotations(string $name, int $number)
    {
        $id = $this->createId($name);

        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/profiles/{$id}",
                ['Accept' => 'application/vnd.elife.profile+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.profile+json; version=1'],
                json_encode([
                    'id' => $id,
                    'name' => [
                        'preferred' => $name,
                        'index' => $name,
                    ],
                ])
            )
        );

        $this->numberOfAnnotations = $number;

        $annotations = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
            $annotations[] = [
                'id' => "annotation-{$i}",
                'access' => 'public',
                'document' => [
                    'title' => 'Article title',
                    'uri' => $this->locatePath('/articles/00001'),
                ],
                'parents' => [],
                'created' => $today->format(ApiSdk::DATE_FORMAT),
                'highlight' => "Annotation {$i} text",
            ];
        }

        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/annotations?by={$id}&page=1&per-page=1&order=desc&use-date=updated",
                ['Accept' => 'application/vnd.elife.annotation-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.annotation-list+json; version=1'],
                json_encode([
                    'total' => $number,
                    'items' => [$annotations[0]],
                ])
            )
        );

        foreach (array_chunk($annotations, $chunk = 10) as $i => $annotationsChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/annotations?by={$id}&page={$page}&per-page={$chunk}&order=desc&use-date=updated",
                    ['Accept' => 'application/vnd.elife.annotation-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.annotation-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => $annotationsChunk,
                    ])
                )
            );
        }
    }

    /**
     * @When /^I go to ([A-Za-z\s]+)'s profile page$/
     */
    public function iGoToProfilePage(string $name)
    {
        $this->visitPath("/profiles/{$this->createId($name)}");
    }

    /**
     * @When /^I load more annotations$/
     */
    public function iLoadMoreAnnotations()
    {
        $this->getSession()->getPage()->clickLink('Load more');
    }

    /**
     * @Then /^I should see his (\d+) most\-recently\-updated annotations in the 'Annotations' list$/
     */
    public function iShouldSeeMostRecentlyUpdatedAnnotationsInTheList(int $number)
    {
        $this->spin(function () use ($number) {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Annotations") + .listing-list > .listing-list__item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);
                $expectedNumber = ($this->numberOfAnnotations - $nthChild + 1);

                $this->assertSession()->elementContains(
                    'css',
                    ".list-heading:contains('Annotations') + .listing-list > .listing-list__item:nth-child({$nthChild})",
                    "Annotation {$expectedNumber} text"
                );
            }
        });
    }

    private function createId(string $name) : string
    {
        return md5($name);
    }
}
