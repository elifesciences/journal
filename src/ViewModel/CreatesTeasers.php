<?php

namespace eLife\Journal\ViewModel;

use eLife\ApiSdk\ApiClient\SubjectsClient;
use eLife\ApiSdk\MediaType;
use eLife\ApiSdk\Result;
use eLife\Patterns\ViewModel\ContextLabel;
use eLife\Patterns\ViewModel\Link;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\all;

trait CreatesTeasers
{
    final private function createContextLabel(array $item) : PromiseInterface
    {
        if (empty($item['subjects'])) {
            return new FulfilledPromise(null);
        }

        return all(array_map(function (string $id) {
            return $this->subjects->getSubject(['Accept' => new MediaType(SubjectsClient::TYPE_SUBJECT, 1)], $id);
        }, $item['subjects']))
            ->then(function (array $subjects) {
                return new ContextLabel(...array_map(function (Result $subject) {
                    return new Link(
                        $subject['name'],
                        $this->urlGenerator->generate('subject', ['id' => $subject['id']])
                    );
                }, $subjects));
            });
    }
}
