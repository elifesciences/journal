<?php

namespace test\eLife\Journal;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class ArticleFixture
{
    public function articleRequest($articleId)
    {
        return new Request(
            'GET',
            'http://api.elifesciences.org/articles/00001',
            [
                'Accept' => [
                    'application/vnd.elife.article-poa+json; version=1',
                    'application/vnd.elife.article-vor+json; version=1',
                ],
            ]
        );
    }

    public function articleVorResponse($sampleName)
    {
        return new Response(
            200,
            ['Content-Type' => 'application/vnd.elife.article-vor+json; version=1'],
            json_encode($this->samples()[$sampleName])
        );
    }

    public function articlePoaResponse($sampleName)
    {
        return new Response(
            200,
            ['Content-Type' => 'application/vnd.elife.article-poa+json; version=1'],
            json_encode($this->samples()[$sampleName])
        );
    }

    public function articleNotFoundResponse()
    {
        return new Response(
            404,
            [
                'Content-Type' => 'application/problem+json',
            ],
            json_encode([
                'title' => 'Not found',
            ])
        );
    }

    private function samples()
    {
        $affiliationOne = [
            'name' => ['Department One', 'Institution One'],
            'address' => [
                'formatted' => ['Locality One', 'Country One'],
                'components' => [
                    'locality' => ['Locality One'],
                    'country' => 'Country One',
                ],
            ],
        ];
        $affiliationTwo = [
            'name' => ['Department Two', 'Institution Two'],
            'address' => [
                'formatted' => ['Locality Two', 'Country Two'],
                'components' => [
                    'locality' => ['Locality Two'],
                    'country' => 'Country Two',
                ],
            ],
        ];
        return [
            'many-authors-and-affiliations' => [
                'status' => 'vor',
                'id' => '00001',
                'version' => 1,
                'type' => 'research-article',
                'doi' => '10.7554/eLife.00001',
                'title' => 'Article title',
                'published' => '2010-01-01T00:00:00+00:00',
                'statusDate' => '2010-01-01T00:00:00+00:00',
                'volume' => 1,
                'elocationId' => 'e00001',
                'copyright' => [
                    'license' => 'CC-BY-4.0',
                    'holder' => 'Bar',
                    'statement' => 'Copyright statement.',
                ],
                'authorLine' => 'Author One et al',
                'authors' => [
                    [
                        'type' => 'person',
                        'name' => [
                            'preferred' => 'Author One',
                            'index' => 'Author One',
                        ],
                        'affiliations' => [
                            $affiliationOne,
                            $affiliationTwo,
                        ],
                    ],
                    [
                        'type' => 'person',
                        'name' => [
                            'preferred' => 'Author Two',
                            'index' => 'Author Two',
                        ],
                    ],
                    [
                        'type' => 'person',
                        'name' => [
                            'preferred' => 'Author Three',
                            'index' => 'Author Three',
                        ],
                        'affiliations' => [
                            $affiliationOne,
                        ],
                    ],
                    [
                        'type' => 'person',
                        'name' => [
                            'preferred' => 'Author Four',
                            'index' => 'Author Four',
                        ],
                        'affiliations' => [
                            [
                                'name' => ['Institution Three'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'on-behalf-of',
                        'onBehalfOf' => 'on behalf of Institution Four',
                    ],
                ],
                'body' => [
                    [
                        'type' => 'section',
                        'id' => 's-1',
                        'title' => 'Introduction',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Fossil hominins were first recognized in the Dinaledi Chamber in the Rising Star cave system in October 2013. During a relatively short excavation, our team recovered an extensive collection of 1550 hominin specimens, representing nearly every element of the skeleton multiple times (Figure 1), including many complete elements and morphologically informative fragments, some in articulation, as well as smaller fragments many of which could be refit into more complete elements. The collection is a morphologically homogeneous sample that can be attributed to no previously-known hominin species. Here we describe this new species, <i>Homo naledi</i>. We have not defined <i>H. naledi</i> narrowly based on a single jaw or skull because the entire body of material has informed our understanding of its biology.',
                            ],
                        ],
                    ],
                ],
            ],
            'a-poa' => [
                'status' => 'poa',
                'id' => '00001',
                'version' => 1,
                'type' => 'research-article',
                'doi' => '10.7554/eLife.00001',
                'title' => 'Article title',
                'published' => '2010-01-01T00:00:00+00:00',
                'statusDate' => '2010-01-01T00:00:00+00:00',
                'volume' => 1,
                'elocationId' => 'e00001',
                'copyright' => [
                    'license' => 'CC-BY-4.0',
                    'holder' => 'Author One',
                    'statement' => 'Copyright statement.',
                ],
                'authorLine' => 'Author One et al',
                'authors' => [
                    [
                        'type' => 'person',
                        'name' => [
                            'preferred' => 'Author One',
                            'index' => 'Author One',
                        ],
                    ],
                ],
            ],
            'content' => [
                'status' => 'vor',
                'id' => '00001',
                'version' => 1,
                'type' => 'research-advance',
                'doi' => '10.7554/eLife.00001',
                'title' => 'Article title',
                'titlePrefix' => 'Title prefix',
                'published' => '2010-01-01T00:00:00+00:00',
                'statusDate' => '2010-01-01T00:00:00+00:00',
                'volume' => 1,
                'elocationId' => 'e00001',
                'copyright' => [
                    'license' => 'CC-BY-4.0',
                    'holder' => 'Bar',
                    'statement' => 'Copyright statement.',
                ],
                'authorLine' => 'Foo Bar',
                'authors' => [
                    [
                        'type' => 'person',
                        'name' => [
                            'preferred' => 'Foo Bar',
                            'index' => 'Bar, Foo',
                        ],
                    ],
                    [
                        'type' => 'group',
                        'name' => 'Baz',
                    ],
                ],
                'abstract' => [
                    'doi' => '10.7554/eLife.09560.001',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Abstract text',
                        ],
                    ],
                ],
                'digest' => [
                    'doi' => '10.7554/eLife.09560.002',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Digest text',
                        ],
                    ],
                ],
                'body' => [
                    [
                        'type' => 'section',
                        'id' => 's-1',
                        'title' => 'Body title',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Body text',
                            ],
                        ],
                    ],
                ],
                'appendices' => [
                    [
                        'id' => 'app1',
                        'doi' => '10.7554/eLife.09560.005',
                        'title' => 'Appendix 1',
                        'content' => [
                            [
                                'type' => 'section',
                                'id' => 'app1-1',
                                'title' => 'Appendix title',
                                'content' => [
                                    [
                                        'type' => 'paragraph',
                                        'text' => 'Appendix text',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'references' => [
                    [
                        'type' => 'journal',
                        'id' => 'bib1',
                        'date' => '2013',
                        'authors' => [
                            [
                                'type' => 'person',
                                'name' => [
                                    'preferred' => 'Person One',
                                    'index' => 'One, Person',
                                ],
                            ],
                        ],
                        'articleTitle' => 'Journal article',
                        'journal' => [
                            'name' => [
                                'A journal',
                            ],
                        ],
                        'pages' => 'In press',
                    ],
                ],
                'acknowledgements' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Acknowledgements text',
                    ],
                ],
                'ethics' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Ethics text',
                    ],
                ],
                'decisionLetter' => [
                    'doi' => '10.7554/eLife.09560.003',
                    'description' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Decision letter description',
                        ],
                    ],
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Decision letter text',
                        ],
                    ],
                ],
                'authorResponse' => [
                    'doi' => '10.7554/eLife.09560.003',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Author response text',
                        ],
                    ],
                ],
            ],
            'content-without-sections' => [
                'status' => 'vor',
                'id' => '00001',
                'version' => 1,
                'type' => 'research-exchange',
                'doi' => '10.7554/eLife.00001',
                'title' => 'Article title',
                'published' => '2010-01-01T00:00:00+00:00',
                'statusDate' => '2010-01-01T00:00:00+00:00',
                'volume' => 1,
                'elocationId' => 'e00001',
                'copyright' => [
                    'license' => 'CC-BY-4.0',
                    'holder' => 'Bar',
                    'statement' => 'Copyright statement.',
                ],
                'authorLine' => 'Foo Bar',
                'authors' => [
                    [
                        'type' => 'person',
                        'name' => [
                            'preferred' => 'Foo Bar',
                            'index' => 'Bar, Foo',
                        ],
                    ],
                ],
                'body' => [
                    [
                        'type' => 'section',
                        'id' => 's-1',
                        'title' => 'Body title',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Body text',
                            ],
                        ],
                    ],
                ],
            ],
            'a-vor' => [
                'status' => 'vor',
                'id' => '00001',
                'version' => 1,
                'type' => 'research-article',
                'doi' => '10.7554/eLife.00001',
                'title' => 'Article title',
                'published' => '2010-01-01T00:00:00+00:00',
                'statusDate' => '2010-01-01T00:00:00+00:00',
                'volume' => 1,
                'elocationId' => 'e00001',
                'copyright' => [
                    'license' => 'CC-BY-4.0',
                    'holder' => 'Bar',
                    'statement' => 'Copyright statement.',
                ],
                'authorLine' => 'Foo Bar',
                'authors' => [
                    [
                        'type' => 'person',
                        'name' => [
                            'preferred' => 'Foo Bar',
                            'index' => 'Bar, Foo',
                        ],
                    ],
                ],
                'body' => [
                    [
                        'type' => 'section',
                        'id' => 's-1',
                        'title' => 'Introduction',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Fossil hominins were first recognized in the Dinaledi Chamber in the Rising Star cave system in October 2013. During a relatively short excavation, our team recovered an extensive collection of 1550 hominin specimens, representing nearly every element of the skeleton multiple times (Figure 1), including many complete elements and morphologically informative fragments, some in articulation, as well as smaller fragments many of which could be refit into more complete elements. The collection is a morphologically homogeneous sample that can be attributed to no previously-known hominin species. Here we describe this new species, <i>Homo naledi</i>. We have not defined <i>H. naledi</i> narrowly based on a single jaw or skull because the entire body of material has informed our understanding of its biology.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
