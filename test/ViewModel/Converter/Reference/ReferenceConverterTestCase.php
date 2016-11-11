<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Model\Reference;
use eLife\Journal\ViewModel\Converter\Reference\CaptionedImageConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use PHPUnit_Framework_TestCase;

abstract class ReferenceConverterTestCase extends PHPUnit_Framework_TestCase
{
    private $serializer;
    protected $converter;
    protected $class;
    protected $viewModelClass;
    protected $context = [];

    /**
     * @before
     * TODO: duplication with other Converter tests
     */
    public function setUpSerializer()
    {
        $httpClient = $this->createMock('eLife\ApiClient\HttpClient');
        $apiSdk = new ApiSdk($httpClient);
        $this->serializer = $apiSdk->getSerializer();
    }

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CaptionedImageConverter();
    }

    /**
     * @test
     * @dataProvider references
     */
    public function it_converts_a_block($data)
    {
        $this->assertInstanceOf(ViewModelConverter::class, $this->converter);

        $reference = $this->serializer->denormalize($data, $this->class);

        $this->assertTrue(
            $this->converter->supports($reference, $this->viewModelClass, $this->context),
            'Converter does not support turning '.get_class($reference).' into '.$this->viewModelClass
        );
        $viewModel = $this->converter->convert($reference);
        $this->assertTrue($viewModel instanceof $this->viewModelClass, 'Failed asserting '.var_export($viewModel, true)." is an instance of $this->viewModelClass");

        $viewModel->toArray();
    }

    abstract public function references();

    /**
     * @test
     */
    public function it_does_not_convert_unsupported_models()
    {
        $reference = $this->serializer->denormalize($this->unsupportedModelData(), Reference::class);

        $this->assertFalse($this->converter->supports($reference), 'Should not support '.var_export($reference, true));
    }

    protected function unsupportedModelData()
    {
        return [
            'type' => 'clinical-trial',
            'id' => '',
            'date' => '1747-01-01',
            'authors' => [],
            'authorsType' => 'authors',
            'title' => 'Efficacy of citrus fruits in curing scurvy',
            'uri' => 'https://en.wikipedia.org/wiki/James_Lind',
        ];
    }
}
