<?php

use Behat\Gherkin\Node\TableNode;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class PeopleContext extends Context
{
    private $subject;
    private $numberOfLeadershipPeople;
    private $numberOfSeniorEditors;
    private $numberOfReviewingEditors;

    /**
     * @Given /^there is the MSA \'([^\']*)\'$/
     */
    public function thereIsTheMSA(string $name)
    {
        $id = $this->createId($name);

        $this->subject = $id;

        $subject = [
            'id' => $id,
            'name' => $name,
            'impactStatement' => "Subject $name impact statement.",
            'image' => [
                'banner' => [
                    'uri' => 'https://www.example.com/iiif/ban%2Fner',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://www.example.com/banner.jpg',
                        'filename' => 'banner.jpg',
                    ],
                    'size' => [
                        'width' => 1800,
                        'height' => 1600,
                    ],
                ],
                'thumbnail' => [
                    'uri' => 'https://www.example.com/iiif/thumb%2Fnail',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://www.example.com/thumbnail.jpg',
                        'filename' => 'thumbnail.jpg',
                    ],
                    'size' => [
                        'width' => 800,
                        'height' => 600,
                    ],
                ],
            ],
        ];

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects?page=1&per-page=100&order=asc',
                ['Accept' => 'application/vnd.elife.subject-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.subject-list+json; version=1'],
                json_encode([
                    'total' => 1,
                    'items' => [$subject],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/subjects/$id",
                ['Accept' => 'application/vnd.elife.subject+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.subject+json; version=1'],
                json_encode($subject)
            )
        );
    }

    /**
     * @Given /^([A-Za-z\s]+) is the Founding Editor\-in\-Chief$/
     */
    public function isTheFoundingEditorInChief(string $name)
    {
        $id = '6d42f4fe';

        $person = [
            'id' => $id,
            'type' => [
                'id' => 'reviewing-editor',
                'label' => 'Founding Editor-in-Chief',
            ],
            'name' => [
                'preferred' => $name,
                'index' => $name,
            ],
        ];

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/people/'.$id,
                ['Accept' => 'application/vnd.elife.person+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.person+json; version=1'],
                json_encode($person)
            )
        );
    }

    /**
     * @Given /^([A-Za-z\s]+) is the Editor\-in\-Chief$/
     */
    public function isTheEditorInChief(string $name)
    {
        $this->numberOfLeadershipPeople = 1;

        $id = $this->createId($name);

        $person = [
            'id' => $id,
            'type' => [
                'id' => 'leadership',
                'label' => 'co-Editor-in-Chief',
            ],
            'name' => [
                'preferred' => $name,
                'index' => $name,
            ],
        ];

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/people?page=1&per-page=1&order=asc&type[]=leadership',
                ['Accept' => 'application/vnd.elife.person-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                json_encode([
                    'total' => 1,
                    'items' => [$person],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/people?page=1&per-page=100&order=asc&type[]=leadership',
                ['Accept' => 'application/vnd.elife.person-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                json_encode([
                    'total' => 1,
                    'items' => [$person],
                ])
            )
        );
    }

    /**
     * @Given /^there are deputy editors:$/
     */
    public function thereAreDeputyEditors(TableNode $deputyEditors)
    {
        $this->numberOfLeadershipPeople = count($deputyEditors->getColumnsHash());

        $people = array_map(function (array $name) {
            return [
                'id' => $this->createId("{$name['Forename']} {$name['Surname']}"),
                'type' => [
                    'id' => 'leadership',
                    'label' => 'Deputy Editor',
                ],
                'name' => [
                    'preferred' => "{$name['Forename']} {$name['Surname']}",
                    'index' => "{$name['Surname']}, {$name['Forename']}",
                ],
            ];
        }, $deputyEditors->getColumnsHash());

        usort($people, function (array $a, array $b) {
            return $a['name']['index'] <=> $b['name']['index'];
        });

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/people?page=1&per-page=1&order=asc&type[]=leadership',
                ['Accept' => 'application/vnd.elife.person-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                json_encode([
                    'total' => 1,
                    'items' => [$people[0]],
                ])
            )
        );

        foreach (array_chunk($people, 100) as $i => $peopleChunk) {
            $page = $i + 1;
            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/people?page=$page&per-page=100&order=asc&type[]=leadership",
                    ['Accept' => 'application/vnd.elife.person-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                    json_encode([
                        'total' => count($people),
                        'items' => $peopleChunk,
                    ])
                )
            );
        }
    }

    /**
     * @Given /^there are senior editors:$/
     */
    public function thereAreSeniorEditors(TableNode $seniorEditors)
    {
        $this->numberOfSeniorEditors = count($seniorEditors->getColumnsHash());

        $people = array_map(function (array $name) {
            return [
                'id' => $this->createId("{$name['Forename']} {$name['Surname']}"),
                'type' => [
                    'id' => 'senior-editor',
                    'label' => 'Senior Editor',
                ],
                'name' => [
                    'preferred' => "{$name['Forename']} {$name['Surname']}",
                    'index' => "{$name['Surname']}, {$name['Forename']}",
                ],
            ];
        }, $seniorEditors->getColumnsHash());

        usort($people, function (array $a, array $b) {
            return $a['name']['index'] <=> $b['name']['index'];
        });

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/people?page=1&per-page=1&order=asc&type[]=senior-editor',
                ['Accept' => 'application/vnd.elife.person-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                json_encode([
                    'total' => 1,
                    'items' => [$people[0]],
                ])
            )
        );

        foreach (array_chunk($people, 100) as $i => $peopleChunk) {
            $page = $i + 1;
            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/people?page=$page&per-page=100&order=asc&type[]=senior-editor",
                    ['Accept' => 'application/vnd.elife.person-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                    json_encode([
                        'total' => count($people),
                        'items' => $peopleChunk,
                    ])
                )
            );
        }
    }

    /**
     * @Given /^there are senior editors for the MSA \'([^\']*)\':$/
     */
    public function thereAreSeniorEditorsForTheMsa(string $subject, TableNode $seniorEditors)
    {
        $subjectId = $this->createId($subject);

        $this->numberOfSeniorEditors = count($seniorEditors->getColumnsHash());

        $people = array_map(function (array $name) use ($subjectId, $subject) {
            return [
                'id' => $this->createId("{$name['Forename']} {$name['Surname']}"),
                'type' => [
                    'id' => $name['Leadership'] ? 'leadership' : 'senior-editor',
                    'label' => $name['Leadership'] ? 'Leadership' : 'Senior Editor',
                ],
                'name' => [
                    'preferred' => "{$name['Forename']} {$name['Surname']}",
                    'index' => "{$name['Surname']}, {$name['Forename']}",
                ],
                'research' => [
                    'expertises' => [
                        [
                            'id' => $subjectId,
                            'name' => $subject,
                        ],
                    ],
                    'focuses' => [],
                ],
            ];
        }, $seniorEditors->getColumnsHash());

        usort($people, function (array $a, array $b) {
            return $a['name']['index'] <=> $b['name']['index'];
        });

        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/people?page=1&per-page=1&order=asc&subject[]=$subjectId&type[]=leadership&type[]=senior-editor",
                ['Accept' => 'application/vnd.elife.person-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                json_encode([
                    'total' => 1,
                    'items' => [$people[0]],
                ])
            )
        );

        foreach (array_chunk($people, 100) as $i => $peopleChunk) {
            $page = $i + 1;
            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/people?page=$page&per-page=100&order=asc&subject[]=$subjectId&type[]=leadership&type[]=senior-editor",
                    ['Accept' => 'application/vnd.elife.person-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                    json_encode([
                        'total' => count($people),
                        'items' => $peopleChunk,
                    ])
                )
            );
        }
    }

    /**
     * @Given /^there are reviewing editors for the MSA \'([^\']*)\':$/
     */
    public function thereAreReviewingEditorsForTheMsa(string $subject, TableNode $reviewingEditors)
    {
        $subjectId = $this->createId($subject);

        $this->numberOfReviewingEditors = count($reviewingEditors->getColumnsHash());

        $people = array_map(function (array $name) use ($subjectId, $subject) {
            return [
                'id' => $this->createId("{$name['Forename']} {$name['Surname']}"),
                'type' => [
                    'id' => 'reviewing-editor',
                    'label' => 'Reviewing Editor',
                ],
                'name' => [
                    'preferred' => "{$name['Forename']} {$name['Surname']}",
                    'index' => "{$name['Surname']}, {$name['Forename']}",
                ],
                'research' => [
                    'expertises' => [
                        [
                            'id' => $subjectId,
                            'name' => $subject,
                        ],
                    ],
                    'focuses' => [],
                ],
            ];
        }, $reviewingEditors->getColumnsHash());

        usort($people, function (array $a, array $b) {
            return $a['name']['index'] <=> $b['name']['index'];
        });

        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/people?page=1&per-page=1&order=asc&subject[]=$subjectId&type[]=reviewing-editor",
                ['Accept' => 'application/vnd.elife.person-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                json_encode([
                    'total' => 1,
                    'items' => [$people[0]],
                ])
            )
        );

        foreach (array_chunk($people, 100) as $i => $peopleChunk) {
            $page = $i + 1;
            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/people?page=$page&per-page=100&order=asc&subject[]=$subjectId&type[]=reviewing-editor",
                    ['Accept' => 'application/vnd.elife.person-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                    json_encode([
                        'total' => count($people),
                        'items' => $peopleChunk,
                    ])
                )
            );
        }
    }

    /**
     * @When /^I go to the People page$/
     */
    public function iGoToThePeoplePage()
    {
        if (null === $this->numberOfLeadershipPeople) {
            $this->mockApiResponse(
                new Request(
                    'GET',
                    'http://api.elifesciences.org/people?page=1&per-page=1&order=asc&type[]=leadership',
                    ['Accept' => 'application/vnd.elife.person-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                    json_encode([
                        'total' => 0,
                        'items' => [],
                    ])
                )
            );
        }

        if (null === $this->numberOfSeniorEditors) {
            $this->mockApiResponse(
                new Request(
                    'GET',
                    'http://api.elifesciences.org/people?page=1&per-page=1&order=asc&type[]=senior-editor',
                    ['Accept' => 'application/vnd.elife.person-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                    json_encode([
                        'total' => 0,
                        'items' => [],
                    ])
                )
            );
        }

        $this->visitPath('/about/people');
    }

    /**
     * @When I go to the People page for the MSA :subject
     */
    public function iGoToThePeoplePageForTheMsa(string $subject)
    {
        if (null === $this->numberOfSeniorEditors) {
            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/people?page=1&per-page=1&order=asc&subject[]={$this->subject}&type[]=leadership&type[]=senior-editor",
                    ['Accept' => 'application/vnd.elife.person-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                    json_encode([
                        'total' => 0,
                        'items' => [],
                    ])
                )
            );
        }

        if (null === $this->numberOfReviewingEditors) {
            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/people?page=1&per-page=1&order=asc&subject[]={$this->subject}&type[]=reviewing-editor",
                    ['Accept' => 'application/vnd.elife.person-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                    json_encode([
                        'total' => 0,
                        'items' => [],
                    ])
                )
            );
        }

        $this->visitPath('/about/people/'.$this->createId($subject));
    }

    /**
     * @Then /^I should see ([A-Za-z\s]+) in the 'Editor\-in\-Chief' list$/
     */
    public function iShouldSeeInTheEditorInChiefList(string $name)
    {
        $this->assertSession()->elementsCount('css', '.list-heading:contains("co-Editor-in-Chief") + .about-profiles > .about-profiles__item', 1);

        $this->assertSession()->elementContains(
            'css',
            '.list-heading:contains("Editor-in-Chief") + .about-profiles > .about-profiles__item:nth-child(1)',
            $name
        );
    }

    /**
     * @Then /^I should see ([A-Za-z\s]+) in the 'Founding Editor\-in\-Chief' list$/
     */
    public function iShouldSeeInTheFoundingEditorInChiefList(string $name)
    {
        $this->assertSession()->elementsCount('css', '.list-heading:contains("Founding Editor-in-Chief") + .about-profiles > .about-profiles__item', 1);

        $this->assertSession()->elementContains(
            'css',
            '.list-heading:contains("Founding Editor-in-Chief") + .about-profiles > .about-profiles__item:nth-child(1)',
            $name
        );
    }

    /**
     * @Then I should see in the :list list:
     */
    public function iShouldSeeInTheList(string $list, TableNode $table)
    {
        $this->assertSession()->elementsCount('css', ".list-heading:contains('$list') + .about-profiles > .about-profiles__item", count($table->getRows()));

        foreach ($table->getColumn(0) as $i => $name) {
            $nthChild = $i + 1;

            $this->assertSession()->elementContains(
                'css',
                ".list-heading:contains('$list') + .about-profiles > .about-profiles__item:nth-child($nthChild)",
                $name
            );
        }
    }

    private function createId(string $name) : string
    {
        return md5($name);
    }
}
