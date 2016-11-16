<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use PHPUnit_Framework_TestCase;
use test\eLife\Journal\ViewModel\Converter\SerializerAwareTestCase;

abstract class BlockConverterTestCase extends PHPUnit_Framework_TestCase
{
    protected $converter;
    protected $class;
    protected $viewModelClass;
    protected $context = [];
    use SerializerAwareTestCase;

    /**
     * @test
     * @dataProvider blocks
     */
    final public function it_converts_a_block($data)
    {
        $this->assertInstanceOf(ViewModelConverter::class, $this->converter);

        $block = $this->serializer->denormalize($data, $this->class);

        $this->assertTrue(
            $this->converter->supports($block, $this->viewModelClass, $this->context),
            'Converter does not support turning '.get_class($block).' into '.$this->viewModelClass
        );
        $viewModel = $this->converter->convert($block);
        $this->assertTrue($viewModel instanceof $this->viewModelClass, 'Failed asserting '.var_export($viewModel, true)." is an instance of $this->viewModelClass");

        $viewModel->toArray();
    }

    abstract public function blocks() : array;

    /**
     * @test
     */
    final public function it_does_not_convert_unsupported_blocks()
    {
        $block = $this->serializer->denormalize($this->unsupportedBlockData(), Block::class);

        $this->assertFalse($this->converter->supports($block), 'Should not support '.var_export($block, true));
    }

    protected function unsupportedBlockData()
    {
        return [
            'type' => 'youtube',
            'id' => '-9JVFCL0fvk',
            'width' => 960,
            'height' => 720,
        ];
    }
}
