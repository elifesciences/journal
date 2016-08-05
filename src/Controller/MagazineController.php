<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\ApiClient\PodcastClient;
use eLife\ApiSdk\MediaType;
use eLife\ApiSdk\Result;
use eLife\Patterns\ViewModel\AudioPlayer;
use eLife\Patterns\ViewModel\AudioSource;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class MagazineController extends Controller
{
    public function listAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Magazine', false,
            'Highlighting the latest research and giving a voice to life and biomedical scientists.');

        $arguments['audio_player'] = $this->get('elife.api_sdk.podcast')
            ->listEpisodes(['Accept' => new MediaType(PodcastClient::TYPE_PODCAST_EPISODE_LIST, 1)], 1, 1)
            ->then(function (Result $result) {
                if (empty($result['items'])) {
                    return null;
                }

                $item = $result['items'][0];

                return new AudioPlayer(
                    'Latest podcast: '.$item['title'],
                    [new AudioSource($item['mp3'], AudioSource::TYPE_MP3)]
                );
            })
            ->otherwise(function (Throwable $e) {
                return null;
            })
        ;

        return new Response($this->get('templating')->render('::magazine.html.twig', $arguments));
    }
}
