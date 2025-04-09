<?php

namespace eLife\Journal\Guzzle;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Psr7\stream_for;

final class RelatedItemsOverrideMiddleware
{
    public function __invoke(callable $handler) : callable
    {
        return function (RequestInterface $request, array $options = []) use (&$handler) {
            return promise_for($handler($request, $options))->then(function (ResponseInterface $response) use ($request) {
                if (!in_array($request->getUri()->getPath(), [
                    '/recommendations/article/100254',
                    '/articles/100254/related',
                ])) {
                    return $response;
                }

                try {
                    $data = json_decode($response->getBody(), true);
                } catch (InvalidArgumentException $e) {
                    return $response;
                }

                $relatedItem = [
                    'id' => '105673',
                    'doi' => '10.1101/2025.01.21.634111',
                    'version' => 1,
                    'type' => 'reviewed-preprint',
                    'status' => 'reviewed',
                    'authorLine' => 'Rachel Kaletsky, Rebecca Moore ... Coleen T Murphy',
                    'title' => 'Molecular Requirements for <em>C. elegans</em> Transgenerational Epigenetic Inheritance of Pathogen Avoidance',
                    'published' => '2025-04-01T14:00:00Z',
                    'reviewedDate' => '2025-04-01T14:00:00Z',
                    'versionDate' => '2025-04-01T14:00:00Z',
                    'statusDate' => '2025-04-01T14:00:00Z',
                    'stage' => 'published',
                    'subjects' => [
                        [
                            'id' => 'genetics-genomics',
                            'name' => 'Genetics and Genomics'
                        ]
                    ],
                    'elifeAssessment' => [
                        'title' => 'eLife Assessment',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'This fundamental study concerns a model for transgenerational epigenetic inheritance, the learned avoidance by C. elegans of the PA14 pathogenic strain of Pseudomonas aeruginosa. The authors test the impact of procedural alterations made in another study, by Gainey et al., which claimed that transgenerational inheritance in this paradigm lacks robustness, despite this observation having been reported in multiple papers from the Murphy lab. The authors of the present study show that by following a non-standard avoidance protocol, Gainey et al. likely biased their measurements in a way that made it hard to observe learned avoidance. The authors also highlight the importance of bacterial growth conditions, showing that expression of the trigger molecule, the bacterial P11 RNA, which is necessary and sufficient to drive the transgenerational inheritance of the avoidance phenotype, is influenced by temperature. As expression of P11 was not verified by Gainey et al., this provides another explanation for the inability to observe transgenerational epigenetic inheritance. Together, the authors provide compelling and powerful arguments that the original phenomenon is robust and that it can be reproduced in the Murphy lab by following their original protocol precisely, including the use of azide to immobilize the worms at the food source. Overall, this study not only provides guidance for investigators in this experimental paradigm, but it also provides additional understanding of the differences between naïve preference, learned preference, and transgenerational epigenetic inheritance. The present study is therefore of broad interest to anyone studying genetics, epigenetics, or learned behavior.'
                            ]
                        ],
                        'id' => 'sa3',
                        'doi' => '10.7554/eLife.105673.1.sa3',
                        'significance' => ['fundamental'],
                        'strength' => ['compelling']
                    ]
                ];

                if (isset($data['total']) && isset($data['items']) && is_array($data['items'])) {
                    $items = array_merge([$relatedItem], $data['items']);
                    $data['items'] = array_slice($items, 0, $data['total']);
                } else {
                    $data = [$relatedItem];
                }

                return $response->withBody(stream_for(json_encode($data)));
            });
        };
    }
}
