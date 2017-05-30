<?php

use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class LabsContext extends Context
{
    private $numberOfPosts;

    /**
     * @Given /^there are (\d+) Labs posts$/
     */
    public function thereAreLabsPosts(int $number)
    {
        $this->numberOfPosts = $number;

        $posts = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
            $posts[] = [
                'id' => (string) $i,
                'title' => 'Post '.$i.' title',
                'published' => $today->format(ApiSdk::DATE_FORMAT),
                'image' => [
                    'thumbnail' => [
                        'uri' => "https://www.example.com/iiif/thumbnail$i",
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
                'impactStatement' => 'Post '.$i.' impact statement',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Post '.$i.' text.',
                    ],
                ],
            ];
        }

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/labs-posts?page=1&per-page=1&order=desc',
                ['Accept' => 'application/vnd.elife.labs-post-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.labs-post-list+json; version=1'],
                json_encode([
                    'total' => $number,
                    'items' => array_map(function (array $post) {
                        unset($post['content']);

                        return $post;
                    }, [$posts[0]]),
                ])
            )
        );

        foreach (array_chunk($posts, 8) as $i => $postsChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/labs-posts?page=$page&per-page=8&order=desc",
                    ['Accept' => 'application/vnd.elife.labs-post-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.labs-post-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => array_map(function (array $post) {
                            unset($post['content']);

                            return $post;
                        }, $postsChunk),
                    ])
                )
            );

            foreach ($postsChunk as $post) {
                $this->mockApiResponse(
                    new Request(
                        'GET',
                        'http://api.elifesciences.org/labs-posts/'.$post['id'],
                        ['Accept' => 'application/vnd.elife.labs-post+json; version=1']
                    ),
                    new Response(
                        200,
                        ['Content-Type' => 'application/vnd.elife.labs-post+json; version=1'],
                        json_encode($post)
                    )
                );
            }
        }
    }

    /**
     * @Given /^I am on a Labs post page$/
     */
    public function iAmOnALabsPostPage()
    {
        $this->thereAreLabsPosts(1);

        $this->visitPath('/labs/1');
    }

    /**
     * @When /^I go the Labs page$/
     */
    public function iGoTheLabsPage()
    {
        $this->visitPath('/labs');
    }

    /**
     * @When /^I load more posts$/
     */
    public function iLoadMorePosts()
    {
        $this->getSession()->getPage()->clickLink('More posts');
    }

    /**
     * @When /^I complete the feedback form$/
     */
    public function iCompleteTheFeedbackForm()
    {
        $this->readyToRecordEmails();

        $page = $this->getSession()->getPage();

        $page->fillField('labs_post_feedback[name]', 'Foo Bar');
        $page->fillField('labs_post_feedback[email]', 'foo@example.com');
        $page->fillField('labs_post_feedback[comment]', "Lorem ipsum dolor sit amet, consectetur adipiscing elit.\n\nVivamus rhoncus turpis quam, sit amet finibus elit pharetra eget.");

        $page->pressButton('Submit');

        $this->recordEmails();
    }

    /**
     * @Then /^I should see the latest (\d+) Labs posts in the 'Latest' list$/
     */
    public function iShouldSeeTheLatestLabsPostsInTheLatestList(int $number)
    {
        $this->spin(function () use ($number) {
            $this->assertSession()
                ->elementsCount('css', '.list-heading:contains("Latest") + .grid-listing > .grid-listing-item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);
                $expectedNumber = ($this->numberOfPosts - $nthChild + 1);

                $this->assertSession()->elementContains(
                    'css',
                    '.list-heading:contains("Latest") + .grid-listing > .grid-listing-item:nth-child('.$nthChild.')',
                    'Post '.$expectedNumber.' title'
                );
            }
        });
    }

    /**
     * @Then /^I should see a 'thank you' message$/
     */
    public function iShouldSeeAThankYouMessage()
    {
        $this->assertSession()
            ->elementContains('css', '.info-bar--success', 'Thanks Foo Bar, we have received your comment.');
    }

    /**
     * @Given /^I should be sent a 'thank you' email$/
     */
    public function iShouldBeSentAThankYouEmail()
    {
        $this->assertEmailSent(['do_not_reply@elifesciences.org' => null], ['foo@example.com' => 'Foo Bar'],
            'Comment on eLife Labs', 'Thanks for your comment. We will respond as soon as we can.

eLife Sciences Publications, Ltd is a limited liability non-profit non-stock corporation incorporated in the State of Delaware, USA, with company number 5030732, and is registered in the UK with company number FC030576 and branch number BR015634 at the address First Floor, 24 Hills Road, Cambridge CB2 1JP.');
    }

    /**
     * @Then /^the completed form should be sent to labs@elifesciences\.org$/
     */
    public function theCompletedFormShouldBeSentToLabsElifesciencesOrg()
    {
        $this->assertEmailSent(['do_not_reply@elifesciences.org' => null], ['labs@elifesciences.org' => null],
            'Comment submitted', 'A comment has been submitted on '.$this->locatePath('/labs/1').'

Name
----
Foo Bar

Email
-----
foo@example.com

Comment
-------
Lorem ipsum dolor sit amet, consectetur adipiscing elit.

Vivamus rhoncus turpis quam, sit amet finibus elit pharetra eget.');
    }
}
