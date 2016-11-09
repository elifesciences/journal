<?php

namespace test\eLife\Journal\ViewModel\Converter;

use PHPUnit_Framework_TestCase;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Paragraph;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ModelConverterTestCase extends PHPUnit_Framework_TestCase
{
    // TODO: check these have sane values before starting the test
    protected $model;
    protected $class;
    protected $viewModelClass;
    protected $converter;
    protected $context = [];
    protected $samples = '*';
    private $serializer;
    
    /**
     * @before
     */
    public function setUpSerializer()
    {
        $httpClient = $this->createMock('eLife\ApiClient\HttpClient');
        $apiSdk = new \eLife\ApiSdk\ApiSdk($httpClient);
        $this->serializer = $apiSdk->getSerializer();
    }

    /**
     * @test
     * @dataProvider samples
     */
    public function it_converts_a_model(string $path)
    {
        $this->assertTrue(file_exists($path), "$path does not exists");
        $file = file_get_contents($path);

        $model = json_decode($file, true);
        $model = $this->dataHook($model);

        $model = $this->serializer->denormalize($model, $this->class);
        $model = $this->modelHook($model);

        $this->assertTrue(
            $this->converter->supports($model, $this->viewModelClass, $this->context),
            "Converter does not support turning " . get_class($model) . " into " . $this->viewModelClass
        );
        $viewModel = $this->converter->convert($model);
        $this->assertTrue($viewModel instanceof $this->viewModelClass);

        $viewModel->toArray();
    }

    public function samples()
    {
        $samples = [];
        foreach ($this->models as $model) {
            $samples = array_merge(
                $samples,
                array_map(
                    function ($path) {
                        return [$path];
                    },
                    $glob = glob("vendor/elife/api/src/samples/{$model}/v1/{$this->samples}.json")
                )
            );
        }
        return $samples;
    }

    /**
     * @test
     */
    public function it_does_not_convert_unsupported_models()
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

    protected function stubUrlGenerator() : UrlGeneratorInterface
    {
        return $this->createMock(UrlGeneratorInterface::class);
    }
}
