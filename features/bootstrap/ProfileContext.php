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
     * @Given /^([A-Za-z\s]+) has (\d+) public annotations?$/
     */
    public function profileHasPublicAnnotations(string $name, int $number)
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

        $this->numberOfPublicAnnotations = $number;

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
                'highlight' => "Public annotation {$i} text",
            ];
        }

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
                    'items' => [$annotations[0]],
                ])
            )
        );

        foreach (array_chunk($annotations, $chunk = 10) as $i => $annotationsChunk) {
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
                        'items' => $annotationsChunk,
                    ])
                )
            );
        }
    }

    /**
     * @Given /^I have (\d+) public and private annotations?$/
     */
    public function profileHasAnnotations(int $number)
    {
        $id = $this->createId($this->name);

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
                        'preferred' => $this->name,
                        'index' => $this->name,
                    ],
                ])
            )
        );

        $this->numberOfAnnotations = $number;

        $annotations = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
            $access = (0 === $i % 2) ? 'restricted' : 'public';

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
     * @Then /^I should see his (\d+) most\-recently\-updated public annotations in the 'Annotations' list$/
     */
    public function iShouldSeeMostRecentlyUpdatedPublicAnnotationsInTheList(int $number)
    {
        $this->spin(function () use ($number) {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Annotations") + .listing-list > .listing-list__item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);

                $expectedNumber = ($this->numberOfPublicAnnotations - $nthChild + 1);

                $this->assertSession()->elementContains(
                    'css',
                    ".list-heading:contains('Annotations') + .listing-list > .listing-list__item:nth-child({$nthChild})",
                    "Public annotation {$expectedNumber} text"
                );
            }
        });
    }

    /**
     * @Then /^I should see my (\d+) most\-recently\-updated annotations in the 'Annotations' list$/
     */
    public function iShouldSeeMostRecentlyUpdatedAnnotationsInTheList(int $number)
    {
        $this->spin(function () use ($number) {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Annotations") + .listing-list > .listing-list__item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);

                $expectedNumber = ($this->numberOfAnnotations - $nthChild + 1);

                $access = (0 === $i % 2) ? 'Restricted' : 'Public';

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
