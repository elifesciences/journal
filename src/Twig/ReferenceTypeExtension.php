<?php

namespace eLife\Journal\Twig;

use eLife\ApiSdk\Model\Reference;
use OutOfBoundsException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ReferenceTypeExtension extends AbstractExtension
{
    private static $references = [
        Reference\BookChapterReference::class => 'book-chapter',
        Reference\BookReference::class => 'book',
        Reference\ClinicalTrialReference::class => 'clinical-trial',
        Reference\ConferenceProceedingReference::class => 'conference-proceeding',
        Reference\DataReference::class => 'data',
        Reference\JournalReference::class => 'journal',
        Reference\PatentReference::class => 'patent',
        Reference\PeriodicalReference::class => 'periodical',
        Reference\PreprintReference::class => 'preprint',
        Reference\ReportReference::class => 'report',
        Reference\SoftwareReference::class => 'software',
        Reference\ThesisReference::class => 'thesis',
        Reference\UnknownReference::class => 'unknown',
        Reference\WebReference::class => 'web',
    ];

    public function getFunctions()
    {
        return [
            new TwigFunction(
                'reference_type',
                [$this, 'getReferenceType']
            ),
        ];
    }

    public function getReferenceType(Reference $reference) : string
    {
        if (empty(self::$references[get_class($reference)])) {
            throw new OutOfBoundsException('Unknown reference type '.get_class($reference));
        }

        return self::$references[get_class($reference)];
    }
}
