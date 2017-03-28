<?php

namespace test\eLife\Journal\ViewModel\Converter;

use Cocur\Slugify\SlugifyInterface;
use ComposerLocator;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Model;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Traversable;
use function GuzzleHttp\json_decode;

abstract class ModelConverterTestCase extends PHPUnit_Framework_TestCase
{
    private static $classes = [
        'annual-report' => Model\AnnualReport::class,
        'article-poa' => Model\ArticlePoA::class,
        'article-vor' => Model\ArticleVoR::class,
        'blog-article' => Model\BlogArticle::class,
        'collection' => Model\Collection::class,
        'event' => Model\Event::class,
        'interview' => Model\Interview::class,
        'labs-experiment' => Model\LabsExperiment::class,
        'medium-article' => Model\MediumArticle::class,
        'person' => Model\Person::class,
        'podcast-episode' => Model\PodcastEpisode::class,
        'podcast-episode-chapter' => Model\PodcastEpisodeChapterModel::class,
        'press-package' => Model\PressPackage::class,
        'subject' => Model\Subject::class,
    ];

    protected $models;
    protected $viewModelClasses;
    protected $converter;
    protected $context = [];

    use SerializerAwareTestCase;

    /**
     * @test
     * @dataProvider samples
     */
    final public function it_converts_a_model(array $model, string $class)
    {
        $this->assertInstanceOf(ViewModelConverter::class, $this->converter);

        $model = $this->serializer->denormalize($model, $class);

        foreach ($this->modelHook($model) as $model) {
            foreach ($this->viewModelClasses as $viewModelClass) {
                $this->assertTrue(
                    $this->converter->supports($model, $viewModelClass, $this->context),
                    'Converter does not support turning '.get_class($model).' into '.$viewModelClass
                );
                $viewModel = $this->converter->convert($model, $viewModelClass, $this->context);
                $this->assertContains(get_class($viewModel), $this->viewModelClasses);

                $viewModel->toArray();
            }
        }
    }

    final public function samples() : Traversable
    {
        $this->assertInternalType('array', $this->models);
        $this->assertInternalType('array', $this->viewModelClasses);
        $this->assertInternalType('array', $this->context);

        foreach ($this->models as $originalModel) {
            switch ($originalModel) {
                case 'medium-article':
                    $model = 'medium-article-list';
                    $list = true;
                    break;
                case 'podcast-episode-chapter':
                    $model = 'recommendations';
                    $type = true;
                    $list = true;
                    break;
                default:
                    $model = $originalModel;
                    $list = false;
            }

            $samples = Finder::create()->files()->in(ComposerLocator::getPath('elife/api')."/dist/samples/{$model}/v1");

            foreach ($samples as $sample) {
                $name = $model.'/v1/'.$sample->getBasename();
                $contents = json_decode($sample->getContents(), true);
                if ($list) {
                    foreach ($contents['items'] as $i => $item) {
                        if ($type ?? false) {
                            if ($originalModel !== $item['type']) {
                                continue;
                            }
                        }
                        yield "$name $i" => [$item, self::$classes[$originalModel]];
                    }
                } else {
                    yield $name => [$contents, self::$classes[$originalModel]];
                }
            }
        }
    }

    /**
     * @test
     */
    final public function it_does_not_convert_unsupported_models()
    {
        $block = $this->serializer->denormalize($this->unsupportedModelData(), Model\Block::class);

        $this->assertFalse($this->converter->supports($block));
    }

    protected function unsupportedModelData() : array
    {
        return [
            'type' => 'youtube',
            'id' => '-9JVFCL0fvk',
            'width' => 960,
            'height' => 720,
        ];
    }

    protected function modelHook(Model\Model $model) : Traversable
    {
        yield $model;
    }

    final protected function stubUrlGenerator() : UrlGeneratorInterface
    {
        return $this->createMock(UrlGeneratorInterface::class);
    }

    final protected function stubSlugify() : SlugifyInterface
    {
        return $this->createMock(SlugifyInterface::class);
    }
}
