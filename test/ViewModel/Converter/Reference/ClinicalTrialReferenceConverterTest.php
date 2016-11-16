<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ClinicalTrialReference;
use eLife\Journal\ViewModel\Converter\Reference\ClinicalTrialReferenceConverter;
use eLife\Patterns\ViewModel\Reference;

final class ClinicalTrialReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $class = ClinicalTrialReference::class;
    protected $viewModelClass = Reference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ClinicalTrialReferenceConverter();
    }

    public function references() : array
    {
        return [
            [
                [
                    'id' => '',
                    'date' => '1747-01-01',
                    'authors' => $this->minimumAuthorsData(),
                    'authorsType' => 'authors',
                    'title' => 'Efficacy of citrus fruits in curing scurvy',
                    'uri' => 'https://en.wikipedia.org/wiki/James_Lind',
                ],
            ],
        ];
    }

    protected function unsupportedModelData()
    {
        return [
            'type' => 'book',
            'id' => '',
            'date' => '2016-01-01',
            'authors' => [],
            'editors' => [],
            'bookTitle' => 'Encyclopedia Galactica',
            'publisher' => [
                'name' => ['Cambridge University Press'],
            ],
        ];
    }
}
