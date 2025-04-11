<?php

namespace eLife\Journal\Controller;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\ReviewedPreprint;
use GuzzleHttp\Promise\Promise;

class HardcodedRecommendationsFor100254
{
    public static function build()
    {
        return [
            new ReviewedPreprint(
                '105673',
                'published',
                '10.1101/2025.01.21.634111',
                'Rachel Kaletsky, Rebecca Moore ... Coleen T Murphy',
                null,
                'Molecular Requirements for <em>C. elegans</em> Transgenerational Epigenetic Inheritance of Pathogen Avoidance',
                DateTimeImmutable::createFromFormat(DATE_ATOM, '2025-04-01T14:00:00Z'),
                DateTimeImmutable::createFromFormat(DATE_ATOM, '2025-04-01T14:00:00Z'),
                DateTimeImmutable::createFromFormat(DATE_ATOM, '2025-04-01T14:00:00Z'),
                DateTimeImmutable::createFromFormat(DATE_ATOM, '2025-04-01T14:00:00Z'),
                'reviewed',
                null,
                null,
                null,
                new EmptySequence(),
                [],
                null,
                new Promise(null),
                1
            )
        ];
    }
}
