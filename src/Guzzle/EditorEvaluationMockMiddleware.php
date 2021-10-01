<?php

namespace eLife\Journal\Guzzle;

use eLife\ApiClient\MediaType;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Psr7\stream_for;

final class EditorEvaluationMockMiddleware
{
    public function __invoke(callable $handler) : callable
    {
        return function (RequestInterface $request, array $options = []) use (&$handler) {
            return promise_for($handler($request, $options))->then(function (ResponseInterface $response) use ($request) {
                try {
                    $mediaType = MediaType::fromString($response->getHeaderLine('Content-Type'));
                    $data = json_decode($response->getBody(), true);
                } catch (InvalidArgumentException $e) {
                    return $response;
                }

                switch ((string) $mediaType) {
                    case 'application/vnd.elife.article-vor+json; version=6':
                        $data = $this->introduceEditorEvaluation($data);
                        break;
                }

                return $response->withBody(stream_for(json_encode($data)));
            });
        };
    }

    private function introduceEditorEvaluation(array $item) : array
    {
        if ('09560' === $item['id']) {
            $item['editorEvaluation'] = [
                'doi' => '10.7554/eLife.09560.sa0',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Collagen is a major component of extracellular matrix. The authors have identified a high-affinity inhibitory collagen receptor LAIR-1 and a soluble decoy receptor LAIR-2 (with even higher binding affinity to collagen), which can be therapeutically targeted to block tumor progression. Dr Meyaard and colleagues have also generated a dimeric LAIR-2 human IgG1 Fc fusion protein NC410 for therapeutic use. With humanized mouse models engrafted with functional human immune systems (PBMC), they have explored the anti-cancer efficacy of NC410 and revealed its impact on modulating immune responses. Furthermore, they extended this study to identify biomarkers of predictive value for NC410-based anti-cancer therapy.',
                    ],
                ],
                'uri' => 'https://sciety.org/articles/activity/10.1101/2020.11.21.391326',
            ];
        }

        return $item;
    }
}
