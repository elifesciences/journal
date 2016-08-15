<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\ApiClient\PodcastClient;
use eLife\ApiSdk\MediaType;
use eLife\ApiSdk\Result;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\HttpFoundation\Response;

final class PodcastController extends Controller
{
    public function listAction() : Response
    {
        $page = 1;
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('eLife podcast');

        $arguments['episodes'] = $this->get('elife.api_sdk.podcast')
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
}
