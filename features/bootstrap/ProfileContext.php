<?php

use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

final class ProfileContext extends Context
{
    private $name;
    private $numberOfAnnotations;
    private $numberOfPublicAnnotations;

    /**
     * @BeforeScenario
     */
    public function enableFeature()
    {
        $this->visitPath('/about?open-sesame');
    }

    /**
     * @Given /^I am ([A-Za-z\s]+)$/
     */
    public function iAm(string $name)
    {
        $this->name = $name;
    }

    /**
     * @Given /^I have logged in$/
     */
    public function iHaveLoggedIn()
    {
        $session = $this->kernel->getContainer()->get('session');

        $id = $this->createId($this->name);

        $token = new PostAuthenticationGuardToken(new OAuthUser($id, $roles = ['ROLE_USER', 'ROLE_OAUTH_USER']), 'main', $roles);

        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->getMink()->getSession()->getDriver()->getClient()->getCookieJar()->set($cookie);
    }

    /**
     * @Given /^([A-Za-z\s]+) has (\d+) public( and private)? annotations?$/
     * @Given /^(I) have (\d+) public( and private)? annotations?$/
     */
    public function profileHasAnnotations(string $name, int $number, bool $restricted = false)
    {
        if ('I' === $name) {
            $name = $this->name;
        }

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
            $access = (0 === $i % 2) && $restricted ? 'restricted' : 'public';

            $annotations[] = [
                'id' => "annotation-{$i}",
                'access' => $access,
                'document' => [
                    'title' => 'Article title',
                    'uri' => $this->locatePath('/articles/00001'),
                ],
                'parents' => [],
                'created' => $today->format(ApiSdk::DATE_FORMAT),
                'highlight' => ucfirst($access)." annotation {$i} text",
            ];
        }

        $publicAnnotations = array_values(array_filter($annotations, function (array $annotation) {
            return 'public' === $annotation['access'];
        }));

        $this->numberOfPublicAnnotations = count($publicAnnotations);

        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/annotations?by={$id}&page=1&per-page=1&order=desc&use-date=updated&access=public",
                ['Accept' => 'application/vnd.elife.annotation-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.annotation-list+json; version=1'],
                json_encode([
                    'total' => $number,
                    'items' => [$publicAnnotations[0]],
                ])
            )
        );

        foreach (array_chunk($publicAnnotations, $chunk = 10) as $i => $publicAnnotationsChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/annotations?by={$id}&page={$page}&per-page={$chunk}&order=desc&use-date=updated&access=public",
                    ['Accept' => 'application/vnd.elife.annotation-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.annotation-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => $publicAnnotationsChunk,
                    ])
                )
            );
        }

        if ($name) {
            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/annotations?by={$id}&page=1&per-page=1&order=desc&use-date=updated&access=restricted",
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
                        "http://api.elifesciences.org/annotations?by={$id}&page={$page}&per-page={$chunk}&order=desc&use-date=updated&access=restricted",
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
    }

    /**
     * @When /^I go to ([A-Za-z\s]+)'s profile page$/
     * @When /^I go to my profile page$/
     */
    public function iGoToProfilePage(string $name = null)
    {
        $this->visitPath("/profiles/{$this->createId($name ?? $this->name)}");
    }

    /**
     * @When /^I load more annotations$/
     */
    public function iLoadMoreAnnotations()
    {
        $this->getSession()->getPage()->clickLink('Load more');
    }

    /**
     * @Then /^I should see (his|my) (\d+) most\-recently\-updated( public)? annotations in the 'Annotations' list$/
     */
    public function iShouldSeeMostRecentlyUpdatedAnnotationsInTheList(string $who, int $number, bool $publicOnly = false)
    {
        $this->spin(function () use ($number, $publicOnly) {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Annotations") + .listing-list > .listing-list__item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);

                $expectedNumber = ($this->numberOfAnnotations - $nthChild + 1);

                $access = (0 === $i % 2) && !$publicOnly ? 'Restricted' : 'Public';

                $this->assertSession()->elementContains(
                    'css',
                    ".list-heading:contains('Annotations') + .listing-list > .listing-list__item:nth-child({$nthChild})",
                    "{$access} annotation {$expectedNumber} text"
                );
            }
        });
    }

    private function createId(string $name) : string
    {
        return md5($name);
    }
}
