<?php

use Behat\Gherkin\Node\TableNode;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class SearchContext extends Context
{
    /**
     * @Given /^I am reading an article:$/
     */
    public function iAmReadingAnArticle(TableNode $table)
    {
        $subjects = array_map(function (string $subject) {
            return [
                'id' => $this->createSubjectId($subject),
                'name' => $subject,
            ];
        }, explode(', ', $table->getRowsHash()['Subjects']));

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                [
                    'Accept' => [
                        'application/vnd.elife.article-poa+json; version=1',
                        'application/vnd.elife.article-vor+json; version=1',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-poa+json; version=1'],
                json_encode([
                    'status' => 'poa',
                    'stage' => 'published',
                    'id' => '00001',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.00001',
                    'title' => 'Article title',
                    'published' => '2010-01-01T00:00:00Z',
                    'versionDate' => '2010-01-01T00:00:00Z',
                    'statusDate' => '2010-01-01T00:00:00Z',
                    'volume' => 1,
                    'elocationId' => 'e00001',
                    'copyright' => [
                        'license' => 'CC0-1.0',
                        'statement' => 'Copyright statement.',
                    ],
                    'subjects' => $subjects,
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001/versions',
                [
                    'Accept' => [
                        'application/vnd.elife.article-history+json; version=1',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=1'],
                json_encode([
                    'versions' => [
                        [
                            'status' => 'poa',
                            'stage' => 'published',
                            'id' => '00001',
                            'version' => 1,
                            'type' => 'research-article',
                            'doi' => '10.7554/eLife.00001',
                            'title' => 'Article title',
                            'published' => '2010-01-01T00:00:00Z',
                            'versionDate' => '2010-01-01T00:00:00Z',
                            'statusDate' => '2010-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e00001',
                            'copyright' => [
                                'license' => 'CC0-1.0',
                                'statement' => 'Copyright statement.',
                            ],
                            'subjects' => $subjects,
                        ],
                    ],
                ])
            )
        );

        $this->visitPath('/content/1/e00001');
    }

    private function createSubjectId(string $subjectName) : string
    {
        return md5($subjectName);
    }

    /**
     * @When /^I click search$/
     */
    public function iClickSearch()
    {
        $this->assertSession()->elementExists('css', 'a[rel="search"]')->click();
    }

    /**
     * @Then /^I should see the option to limit the search to \'([^\']*)\'$/
     */
    public function iShouldSeeTheOptionToLimitTheSearchTo(string $subject)
    {
        $this->spin(function () use ($subject) {
            $this->assertSession()->fieldExists('Limit my search to '.$subject);
        });
    }
}
