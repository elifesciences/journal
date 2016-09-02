<?php

namespace eLife\Journal\Controller;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\PodcastClient;
use eLife\ApiClient\Exception\BadResponse;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\Patterns\ViewModel\AudioPlayer;
use eLife\Patterns\ViewModel\AudioSource;
use eLife\Patterns\ViewModel\BackgroundImage;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\ContextLabel;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Image;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Picture;
use eLife\Patterns\ViewModel\PodcastDownload;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class PodcastController extends Controller
{
    public function listAction() : Response
    {
        $page = 1;
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('eLife podcast');

        $arguments['episodes'] = $this->get('elife.api_client.podcast')
            ->listEpisodes(['Accept' => new MediaType(PodcastClient::TYPE_PODCAST_EPISODE_LIST, 1)], $page, $perPage)
            ->then(function (Result $result) {
                $teasers = [];

                foreach ($result['items'] as $episode) {
                    $teasers[] = $this->get('elife.journal.view_model.factory.teaser_grid')
                        ->forPodcastEpisode($episode);
                }

                return $teasers;
            });

        return new Response($this->get('templating')->render('::podcast.html.twig', $arguments));
    }

    public function episodeAction(int $number) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['episode'] = $this->get('elife.api_client.podcast')
            ->getEpisode(['Accept' => new MediaType(PodcastClient::TYPE_PODCAST_EPISODE, 1)], $number)
            ->otherwise(function (Throwable $e) {
                if ($e instanceof BadResponse && 404 === $e->getResponse()->getStatusCode()) {
                    throw new NotFoundHttpException('Episode not found', $e);
                }
            });

        $arguments['contentHeader'] = $arguments['episode']
            ->then(function (Result $episode) {
                return ContentHeaderNonArticle::podcast($episode['title'], false, 'Episode '.$episode['number'], null,
                    Meta::withLink(new Link('Podcast', $this->get('router')->generate('podcast')),
                        new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $episode['published']))),
                    new BackgroundImage(
                        $episode['image']['sizes']['2:1'][900],
                        $episode['image']['sizes']['2:1'][1800]
                    ),
                    new PodcastDownload(
                        $episode['mp3'],
                        new Picture(
                            [
                                [
                                    'srcset' => $this->get('puli.url_generator')
                                        ->generateUrl('/elife/patterns/assets/img/icons/download-full-reverse.svg'),
                                    'media' => '(min-width: 35em)',
                                    'type' => 'image/svg+xml',
                                ],
                                [
                                    'srcset' => $this->get('puli.url_generator')
                                        ->generateUrl('/elife/patterns/assets/img/icons/download-full-reverse-1x.png'),
                                    'media' => '(min-width: 35em)',
                                ],
                                [
                                    'srcset' => $this->get('puli.url_generator')
                                        ->generateUrl('/elife/patterns/assets/img/icons/download-reverse.svg'),
                                    'type' => 'image/svg+xml',
                                ],
                            ],
                            new Image(
                                $this->get('puli.url_generator')
                                    ->generateUrl('/elife/patterns/assets/img/icons/download-full-reverse-1x.png'),
                                [
                                    88 => $this->get('puli.url_generator')
                                        ->generateUrl('/elife/patterns/assets/img/icons/download-full-reverse-2x.png'),
                                    44 => $this->get('puli.url_generator')
                                        ->generateUrl('/elife/patterns/assets/img/icons/download-full-reverse-1x.png'),
                                ],
                                'Download icon'
                            )
                        )
                    )
                );
            });

        $arguments['audioPlayer'] = $arguments['episode']
            ->then(function (Result $episode) {
                return new AudioPlayer($episode['title'], [new AudioSource($episode['mp3'], AudioSource::TYPE_MP3)]);
            });

        $arguments['leadParas'] = $arguments['episode']
            ->then(function (Result $episode) {
                return new LeadParas([new LeadPara($episode['impactStatement'])]);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['chapters'] = $arguments['episode']
            ->then(function (Result $episode) {
                return ListingTeasers::basic(
                    array_map(function (array $chapter) {
                        $minutes = floor($chapter['time'] / 60);
                        $seconds = str_pad($chapter['time'] % 60, 2, '0', STR_PAD_LEFT);

                        return Teaser::chapterListingItem(
                            $chapter['title'],
                            null,
                            $chapter['impactStatement'] ?? null,
                            new ContextLabel(new Link(sprintf('%s:%s', $minutes, $seconds)))
                        );
                    }, $episode['chapters']),
                    'Chapters'
                );
            });

        $arguments['related'] = $arguments['episode']
            ->then(function (Result $episode) {
                $articles = [];

                foreach ($episode['chapters'] as $chapter) {
                    $articles[] = $chapter['content'][0];
                }

                return $this->get('elife.journal.view_model.factory.listing_teaser_secondary')
                    ->forItems($articles, 'Related');
            });

        return new Response($this->get('templating')->render('::podcast-episode.html.twig', $arguments));
    }
}
