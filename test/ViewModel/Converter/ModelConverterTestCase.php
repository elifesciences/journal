<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use test\eLife\Journal\PuliAwareTestCase;

abstract class ModelConverterTestCase extends PHPUnit_Framework_TestCase
{
    protected $models;
    protected $class;
    protected $viewModelClass;
    protected $converter;
    protected $context = [];
    protected $selectSamples = false;
    use PuliAwareTestCase;
    use SerializerAwareTestCase;

    /**
     * @test
     * @dataProvider samples
     */
    final public function it_converts_a_model(string $body)
    {
        $this->assertInstanceOf(ViewModelConverter::class, $this->converter);

        $model = json_decode($body, true);
        $model = $this->dataHook($model);

        $model = $this->serializer->denormalize($model, $this->class);
        $model = $this->modelHook($model);

        $this->assertTrue(
            $this->converter->supports($model, $this->viewModelClass, $this->context),
            'Converter does not support turning '.get_class($model).' into '.$this->viewModelClass
        );
        $viewModel = $this->converter->convert($model);
        $this->assertTrue($viewModel instanceof $this->viewModelClass);

        $viewModel->toArray();
    }

    final public function samples()
    {
        // @beforeClass not called on data providers
        $this->setUpPuli();

        $this->assertInternalType('array', $this->models);
        $this->assertInternalType('string', $this->class);
        $this->assertInternalType('string', $this->viewModelClass);
        $this->assertInternalType('array', $this->context);

        $samples = [];
        foreach ($this->models as $model) {
            $folder = self::$puli->find("/elife/api/samples/{$model}/v1/*");

            foreach ($folder as $sample) {
                if ($this->selectSamples) {
                    if (!in_array($sample->getName(), $this->selectSamples)) {
                        continue;
                    }
                }
                $name = $model.'/v1/'.$sample->getName();
                $samples[$name] = ['body' => $sample->getBody()];
            }
        }

        return $samples;
    }

    /**
     * @test
     */
    final public function it_does_not_convert_unsupported_models()
    {
        $block = [
            'type' => 'youtube',
            'id' => '-9JVFCL0fvk',
            'width' => 960,
            'height' => 720,
        ];

        $block = $this->serializer->denormalize($block, Block::class);

        $this->assertFalse($this->converter->supports($block));
    }

    protected function dataHook(array $model) : array
    {
        return $model;
    }

    /**
     * @return object
     */
    protected function modelHook(Model $model)
    {
        return $model;
    }

    final protected function stubUrlGenerator() : UrlGeneratorInterface
    {
        return $this->createMock(UrlGeneratorInterface::class);
    }
}
