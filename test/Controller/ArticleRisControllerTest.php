<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\WebTestCase;
use Traversable;

final class ArticleRisControllerTest extends WebTestCase
{
    /**
     * @test
     * @dataProvider risProvider
     */
    public function it_displays_ris(string $status, array $json, string $expected)
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=5']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-'.$status.'+json; version='.('vor' === $status ? '4' : '3')],
                json_encode($json)
            )
        );

        $client->request('GET', '/articles/00001.ris');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('application/x-research-info-systems', $client->getResponse()->headers->get('Content-Type'));
        $this->assertSame(preg_replace('~\R~u', "\r\n", $expected), trim($client->getResponse()->getContent()));
    }

    public function risProvider() : Traversable
    {
        yield 'minimum PoA' => [
            'poa',
            [
                'status' => 'poa',
                'id' => '00001',
                'version' => 1,
                'type' => 'research-article',
                'doi' => '10.7554/eLife.00001',
                'title' => 'Title',
                'stage' => 'published',
                'published' => '2016-01-02T00:00:00Z',
                'statusDate' => '2016-01-02T00:00:00Z',
                'versionDate' => '2016-01-02T00:00:00Z',
                'volume' => 1,
                'elocationId' => 'e00001',
                'copyright' => [
                    'license' => 'CC0-1.0',
                    'statement' => 'Statement.',
                ],
                'authorLine' => 'Foo Bar et al.',
                'authors' => [
                    [
                        'type' => 'person',
                        'name' => [
                            'preferred' => 'Foo Bar',
                            'index' => 'Bar, Foo',
                        ],
                    ],
                ],
            ],
            <<<'EOT'
TY  - JOUR
TI  - Title
AU  - Bar, Foo
VL  - 1
PY  - 2016
DA  - 2016/01/02
SP  - e00001
C1  - eLife 2016;1:e00001
DO  - 10.7554/eLife.00001
UR  - https://doi.org/10.7554/eLife.00001
JF  - eLife
SN  - 2050-084X
PB  - eLife Sciences Publications, Ltd
ER  -
EOT
            ,
        ];

        yield 'complete VoR' => [
            'vor',
            [
                'status' => 'vor',
                'id' => '00001',
                'version' => 3,
                'type' => 'research-article',
                'doi' => '10.7554/eLife.00001',
                'title' => '<i>Title</i>',
                'titlePrefix' => 'Prefix',
                'stage' => 'published',
                'published' => '2016-01-02T00:00:00Z',
                'statusDate' => '2016-02-01T00:00:00Z',
                'versionDate' => '2016-03-01T00:00:00Z',
                'volume' => 1,
                'issue' => 2,
                'elocationId' => 'e00001',
                'copyright' => [
                    'license' => 'CC-BY-4.0',
                    'holder' => 'Author One et al.',
                    'statement' => 'Statement.',
                ],
                'authorLine' => 'Author One et al.',
                'authors' => [
                    [
                        'type' => 'person',
                        'name' => [
                            'preferred' => 'Author One',
                            'index' => 'One, Author',
                        ],
                    ],
                    [
                        'type' => 'group',
                        'name' => 'Group One',
                        'people' => [
                            [
                                'name' => [
                                    'preferred' => 'Author Two',
                                    'index' => 'Two, Author',
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'group',
                        'name' => 'Group Two',
                        'groups' => [
                            'Sub-group 1' => [
                                [
                                    'name' => [
                                        'preferred' => 'Author Three',
                                        'index' => 'Three, Author',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'on-behalf-of',
                        'onBehalfOf' => 'on behalf of An Organisation',
                    ],
                ],
                'reviewers' => [
                    [
                        'name' => [
                            'preferred' => 'Reviewer One',
                            'index' => 'One, Reviewer',
                        ],
                        'role' => 'Reviewing editor',
                    ],
                    [
                        'name' => [
                            'preferred' => 'Reviewer Two',
                            'index' => 'Two, Reviewer',
                        ],
                        'role' => 'Reviewing editor',
                    ],
                ],
                'keywords' => [
                    '<i>Keyword one</i>',
                    'Keyword two',
                ],
                'abstract' => [
                    'doi' => '10.7554/eLife.00001.001',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Lorem <b>ipsum</b> <i>dolor</i> <span class="underline">sit</span> <span class="monospace">amet</span>, <span class="small-caps">consectetur</span> <sub>adipiscing</sub> <sup>elit</sup>.',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => '<b><i>D<sub>u</sub>i</i>s</b> ornare &amp;%$#_{}~^\&gt;&lt; nunc.',
                        ],
                    ],
                ],
                'body' => [
                    [
                        'type' => 'section',
                        'id' => 's-1',
                        'title' => 'Section',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Text.',
                            ],
                        ],
                    ],
                ],
            ],
            <<<EOT
TY  - JOUR
TI  - Title
AU  - One, Author
AU  - Group One
AU  - Group Two
A2  - One, Reviewer
A2  - Two, Reviewer
VL  - 1
IS  - 2
PY  - 2016
DA  - 2016/01/02
SP  - e00001
C1  - eLife 2016;1:e00001
DO  - 10.7554/eLife.00001
UR  - https://doi.org/10.7554/eLife.00001
AB  - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis ornare &%$#_{}~^\>< nunc.
KW  - Keyword one
KW  - Keyword two
JF  - eLife
SN  - 2050-084X
PB  - eLife Sciences Publications, Ltd
ER  -
EOT
            ,
        ];

        yield 'structured abstract' => [
            'poa',
            [
                'status' => 'poa',
                'id' => '00001',
                'version' => 1,
                'type' => 'research-article',
                'doi' => '10.7554/eLife.00001',
                'title' => 'Title',
                'stage' => 'published',
                'published' => '2016-01-02T00:00:00Z',
                'statusDate' => '2016-01-02T00:00:00Z',
                'versionDate' => '2016-01-02T00:00:00Z',
                'volume' => 1,
                'elocationId' => 'e00001',
                'copyright' => [
                    'license' => 'CC0-1.0',
                    'statement' => 'Statement.',
                ],
                'authorLine' => 'Foo Bar et al.',
                'authors' => [
                    [
                        'type' => 'person',
                        'name' => [
                            'preferred' => 'Foo Bar',
                            'index' => 'Bar, Foo',
                        ],
                    ],
                ],
                'abstract' => [
                    'content' => [
                        [
                            'type' => 'section',
                            'title' => 'Introduction',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Abstract 00001.',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            <<<'EOT'
TY  - JOUR
TI  - Title
AU  - Bar, Foo
VL  - 1
PY  - 2016
DA  - 2016/01/02
SP  - e00001
C1  - eLife 2016;1:e00001
DO  - 10.7554/eLife.00001
UR  - https://doi.org/10.7554/eLife.00001
AB  - Introduction. Abstract 00001.
JF  - eLife
SN  - 2050-084X
PB  - eLife Sciences Publications, Ltd
ER  -
EOT
            ,
        ];
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_article_is_not_found()
    {
        $client = static::createClient();

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=5']
            ),
            new Response(
                404,
                [
                    'Content-Type' => 'application/problem+json',
                ],
                json_encode([
                    'title' => 'Not found',
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
                404,
                [
                    'Content-Type' => 'application/problem+json',
                ],
                json_encode([
                    'title' => 'Not found',
                ])
            )
        );

        $client->request('GET', '/articles/00001.ris');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_article_is_not_published()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=5']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-poa+json; version=3'],
                json_encode([
                    'status' => 'poa',
                    'id' => '00001',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.00001',
                    'title' => 'Title',
                    'stage' => 'preview',
                    'volume' => 1,
                    'elocationId' => 'e00001',
                    'copyright' => [
                        'license' => 'CC0-1.0',
                        'statement' => 'Statement.',
                    ],
                    'authorLine' => 'Foo Bar et al.',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'Foo Bar',
                                'index' => 'Bar, Foo',
                            ],
                        ],
                    ],
                ])
            )
        );

        $client->request('GET', '/articles/00001.ris');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
}
