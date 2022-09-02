<?php

namespace test\eLife\Journal\Guzzle;

use eLife\Journal\Guzzle\PodcastEpisodeMp3RewritingMiddleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use test\eLife\Journal\Assertions;
use test\eLife\Journal\KernelTestCase;
use test\eLife\Journal\Providers;
use Traversable;
use function GuzzleHttp\Psr7\str;

final class PodcastEpisodeMp3RewritingMiddlewareTest extends KernelTestCase
{
    use Assertions;
    use Providers;

    /**
     * @test
     * @dataProvider responseProvider
     */
    public function it_rewrites_responses(string $mediaType, array $realResponse, array $expectedResponse)
    {
        $validator = self::bootKernel()->getContainer()->get('elife.api_validator.validator');
        $middleware = new PodcastEpisodeMp3RewritingMiddleware();

        $request = new Request(
            'GET',
            'http://api.elifesciences.org/foo',
            ['Accept' => $mediaType]
        );

        $realResponse = new Response(
            200,
            ['Content-Type' => $mediaType],
            json_encode($realResponse)
        );
        $expectedResponse = new Response(
            200,
            ['Content-Type' => $mediaType],
            json_encode($expectedResponse)
        );

        $validator->validate($realResponse);
        $validator->validate($expectedResponse);

        $actualResponse = $middleware(function (RequestInterface $realRequest) use ($request, $realResponse) {
            $this->assertSame(str($request), str($realRequest));

            return $realResponse;
        })($request)->wait();

        $this->assertSame(str($expectedResponse), str($actualResponse));
    }

    public function responseProvider() : Traversable
    {
        return $this->arrayProvider([
            'application/vnd.elife.collection+json; version=2' => [
                $this->createCollection(1),
                $this->createCollection(1, 'https://downloads.nakeddiscovery.com/downloads/active/episode1.mp3'),
            ],
            'application/vnd.elife.promotional-collection+json; version=1' => [
                $this->createCollection(1),
                $this->createCollection(1, 'https://downloads.nakeddiscovery.com/downloads/active/episode1.mp3'),
            ],
            'application/vnd.elife.community-list+json; version=1' => [
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticleVoR(),
                        $this->createPodcastEpisode(2),
                    ],
                ],
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticleVoR(),
                        $this->createPodcastEpisode(2, 'https://downloads.nakeddiscovery.com/downloads/active/episode2.mp3'),
                    ],
                ],
            ],
            'application/vnd.elife.cover-list+json; version=1' => [
                [
                    'total' => 1,
                    'items' => [
                        [
                            'title' => 'Cover title',
                            'image' => $this->createImage(),
                            'item' => $this->createPodcastEpisode(3),
                        ],
                    ],
                ],
                [
                    'total' => 1,
                    'items' => [
                        [
                            'title' => 'Cover title',
                            'image' => $this->createImage(),
                            'item' => $this->createPodcastEpisode(3, 'https://downloads.nakeddiscovery.com/downloads/active/episode3.mp3'),
                        ],
                    ],
                ],
            ],
            'application/vnd.elife.highlight-list+json; version=3' => [
                [
                    'total' => 1,
                    'items' => [
                        [
                            'title' => 'Highlight title',
                            'item' => $this->createPodcastEpisode(1),
                        ],
                    ],
                ],
                [
                    'total' => 1,
                    'items' => [
                        [
                            'title' => 'Highlight title',
                            'item' => $this->createPodcastEpisode(1, 'https://downloads.nakeddiscovery.com/downloads/active/episode1.mp3'),
                        ],
                    ],
                ],
            ],
            'application/vnd.elife.podcast-episode+json; version=1' => [
                $this->createPodcastEpisode(2, null, false),
                $this->createPodcastEpisode(2, 'https://downloads.nakeddiscovery.com/downloads/active/episode2.mp3', false),
            ],
            'application/vnd.elife.podcast-episode-list+json; version=1' => [
                [
                    'total' => 3,
                    'items' => [
                        $this->createPodcastEpisode(4, 'http://web/tests/blank.mp3'),
                        $this->createPodcastEpisode(3),
                        $this->createPodcastEpisode(2),
                        $this->createPodcastEpisode(1),
                    ],
                ],
                [
                    'total' => 3,
                    'items' => [
                        $this->createPodcastEpisode(4, 'http://web/tests/blank.mp3'),
                        $this->createPodcastEpisode(3, 'https://downloads.nakeddiscovery.com/downloads/active/episode3.mp3'),
                        $this->createPodcastEpisode(2, 'https://downloads.nakeddiscovery.com/downloads/active/episode2.mp3'),
                        $this->createPodcastEpisode(1, 'https://downloads.nakeddiscovery.com/downloads/active/episode1.mp3'),
                    ],
                ],
            ],
            'application/vnd.elife.search+json; version=1;' => [
                [
                    'total' => 2,
                    'items' => [
                        $this->createPodcastEpisode(3),
                        $this->createArticleVoR('00002'),
                    ],
                    'subjects' => [],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 2,
                        'research-communication' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'review-article' => 0,
                        'scientific-correspondence' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-post' => 0,
                        'podcast-episode' => 0,
                    ],
                ],
                [
                    'total' => 2,
                    'items' => [
                        $this->createPodcastEpisode(3, 'https://downloads.nakeddiscovery.com/downloads/active/episode3.mp3'),
                        $this->createArticleVoR('00002'),
                    ],
                    'subjects' => [],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 2,
                        'research-communication' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'review-article' => 0,
                        'scientific-correspondence' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-post' => 0,
                        'podcast-episode' => 0,
                    ],
                ],
            ],
        ]);
    }

    private function createArticleVoR(string $id = null) : array
    {
        return [
            'status' => 'vor',
            'stage' => 'published',
            'id' => $id ?? '00001',
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
                'license' => 'CC-BY-4.0',
                'holder' => 'Bar',
                'statement' => 'Copyright statement.',
            ],
        ];
    }

    private function createCollection(int $number = null, string $newMp3 = null) : array
    {
        return [
            'id' => '1',
            'title' => 'Collection title',
            'published' => '2010-01-01T00:00:00Z',
            'image' => [
                'thumbnail' => $this->createImage(),
                'banner' => $this->createImage(),
            ],
            'selectedCurator' => $this->createPerson(),
            'content' => [
                $this->createArticleVoR(),
            ],
            'curators' => [$this->createPerson()],
            'podcastEpisodes' => [
                $this->createPodcastEpisode($number, $newMp3),
            ],
        ];
    }

    private function createPodcastEpisode(int $number = null, string $newMp3 = null, bool $snippet = true) : array
    {
        $podcast = [
            'number' => $number,
            'type' => 'podcast-episode',
            'title' => 'Podcast episode '.$number,
            'published' => '2010-01-01T00:00:00Z',
            'image' => [
                'thumbnail' => $this->createImage(),
            ],
            'sources' => [
                [
                    'mediaType' => 'audio/mpeg',
                    'uri' => $newMp3 ?? 'https://www.example.com/episode'.$number.'.mp3',
                ],
            ],
        ];

        if (!$snippet) {
            unset($podcast['type']);

            $podcast['chapters'] = [
                [
                    'number' => 1,
                    'title' => 'Chapter 1',
                    'time' => 0,
                    'content' => [
                        $this->createArticleVoR(),
                    ],
                ],
            ];

            $podcast['image']['banner'] = $this->createImage();
        }

        return $podcast;
    }

    private function createImage() : array
    {
        return [
            'uri' => 'https://www.example.com/iiif/iden%2Ftifer',
            'alt' => '',
            'source' => [
                'mediaType' => 'image/jpeg',
                'uri' => 'https://www.example.com/image.jpg',
                'filename' => 'image.jpg',
            ],
            'size' => [
                'width' => 800,
                'height' => 600,
            ],
        ];
    }

    private function createPerson() : array
    {
        return [
            'id' => 'person1',
            'type' => [
                'id' => 'senior-editor',
                'label' => 'Senior Editor',
            ],
            'name' => [
                'preferred' => 'Person 1',
                'index' => 'Person 1',
            ],
        ];
    }
}
