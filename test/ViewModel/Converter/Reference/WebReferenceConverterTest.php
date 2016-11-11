<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\WebReference;
use eLife\Journal\ViewModel\Converter\Reference\WebReferenceConverter;
use eLife\Patterns\ViewModel\Reference;

final class WebReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $class = WebReference::class;
    protected $viewModelClass = Reference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new WebReferenceConverter();
    }

    public function references()
    {
        return [
            [
                [
                    'id' => '',
                    'date' => '2016-01-01',
                    'authors' => [
                    ],
                    'title' => 'Example.com',
                    'uri' => 'https://example.com'
                ],
            ],
        ];
    }
}
