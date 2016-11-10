<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\CaptionedImageConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use PHPUnit_Framework_TestCase;

abstract class BlockConverterTestCase extends PHPUnit_Framework_TestCase
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
     * @dataProvider blocks
     */
    public function it_converts_a_block($data)
    {
        $this->assertInstanceOf(ViewModelConverter::class, $this->converter);

        $block = $this->serializer->denormalize($data, $this->class);

        $this->assertTrue(
            $this->converter->supports($block, $this->viewModelClass, $this->context),
            'Converter does not support turning '.get_class($block).' into '.$this->viewModelClass
        );
        $viewModel = $this->converter->convert($block);
        $this->assertTrue($viewModel instanceof $this->viewModelClass);

        $viewModel->toArray();
    }

    public function blocks()
    {
        return [
            [
                [
                    'alt' => 'Image 1',
                    'uri' => 'https://example.com/image1',
                    'title' => 'An image\'s caption',
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function it_does_not_convert_unsupported_models()
    {
        $block = $this->serializer->denormalize($this->unsupportedModelData(), Block::class);

        $this->assertFalse($this->converter->supports($block));
    }

    protected function unsupportedModelData()
    {
        return [
            'type' => 'youtube',
            'id' => '-9JVFCL0fvk',
            'width' => 960,
            'height' => 720,
        ];
    }
}
