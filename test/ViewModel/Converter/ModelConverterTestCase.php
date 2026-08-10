<?php

namespace test\eLife\Journal\ViewModel\Converter;

use ComposerLocator;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Model;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Traversable;
use function GuzzleHttp\json_decode;

abstract class ModelConverterTestCase extends TestCase
{
    private static $classes = [
        'annotation' => Model\Annotation::class,
        'annual-report' => Model\AnnualReport::class,
        'article-poa' => Model\ArticlePoA::class,
        'article-vor' => Model\ArticleVoR::class,
        'blog-article' => Model\BlogArticle::class,
        'collection' => Model\Collection::class,
        'cover' => Model\Cover::class,
        'digest' => Model\Digest::class,
        'event' => Model\Event::class,
        'external-article' => Model\ExternalArticle::class,
        'highlight' => Model\Highlight::class,
        'interview' => Model\Interview::class,
        'job-advert' => Model\JobAdvert::class,
        'labs-post' => Model\LabsPost::class,
        'person' => Model\Person::class,
        'podcast-episode' => Model\PodcastEpisode::class,
        'podcast-episode-chapter' => Model\PodcastEpisodeChapterModel::class,
        'press-package' => Model\PressPackage::class,
        'profile' => Model\Profile::class,
        'promotional-collection' => Model\PromotionalCollection::class,
        'reviewed-preprint' => Model\ReviewedPreprint::class,
        'subject' => Model\Subject::class,
    ];

    protected $models;
    protected $viewModelClasses;
    protected $converter;
    protected $context = [];

    use SerializerAwareTestCase;

    #[Test]
    final public function it_is_a_view_model_converter()
    {
        $this->assertInstanceOf(ViewModelConverter::class, $this->converter);
    }

    #[Test]
    #[DataProvider('samples')]
    final public function it_converts_a_model($model, string $viewModelClass)
    {
        $this->assertTrue(
            $this->converter->supports($model, $viewModelClass, $this->context),
            'Converter does not support turning '.get_class($model).' into '.$viewModelClass
        );
        $viewModel = $this->converter->convert($model, $viewModelClass, $this->context);
        $this->assertContains(get_class($viewModel), $this->viewModelClasses);

        $viewModel->toArray();
    }

    public static function samples() : Traversable
    {
        $instance = (new ReflectionClass(static::class))->newInstanceWithoutConstructor();
        $instance->setUpSerializer();

        foreach ($instance->findSamples() as $sample) {
            $model = $sample[0];
            $class = $sample[1];

            $model = $instance->serializer->denormalize($model, $class);

            foreach ($instance->modelHook($model) as $model) {
                foreach ($instance->viewModelClasses as $viewModelClass) {
                    yield [$model, $viewModelClass];
                }
            }
        }
    }

    /**
     * Denormalizes a real sample fixture file directly into the given model class, for cases where
     * no fixture combines the wrapper and item types needed (e.g. a highlight wrapping an item type
     * that no highlight-list sample happens to contain).
     */
    protected static function denormalizeSample(string $path, string $class)
    {
        return self::denormalizeData(json_decode(file_get_contents($path), true), $class);
    }

    protected static function denormalizeData(array $data, string $class)
    {
        $instance = (new ReflectionClass(static::class))->newInstanceWithoutConstructor();
        $instance->setUpSerializer();

        return $instance->serializer->denormalize($data, $class);
    }

    private function findSamples() : Traversable
    {
        $this->assertIsArray($this->models);
        $this->assertIsArray($this->viewModelClasses);
        $this->assertIsArray($this->context);

        foreach ($this->models as $originalModel) {
            $model = $originalModel;
            $list = false;
            $version = 1;
            switch ($originalModel) {
                case 'annotation':
                    $model = 'annotation-list';
                    $list = true;
                    break;
                case 'cover':
                    $model = 'cover-list';
                    $list = true;
                    break;
                case 'highlight':
                    $model = 'highlight-list';
                    $list = true;
                    $version = 3;
                    break;
                case 'external-article':
                case 'podcast-episode-chapter':
                    $model = 'recommendations';
                    $type = true;
                    $list = true;
                    break;
                case 'annual-report':
                case 'blog-article':
                case 'collection':
                case 'event':
                case 'interview':
                case 'labs-post':
                    $version = 2;
                    break;
                case 'article-poa':
                case 'press-package':
                    $version = 3;
                    break;
                case 'article-vor':
                    $version = 7;
                    break;
            }

            $samples = Finder::create()->files()->in(ComposerLocator::getPath('elife/api')."/dist/samples/{$model}/v{$version}");

            foreach ($samples as $sample) {
                $name = "{$model}/v{$version}/{$sample->getBasename()}";
                $contents = json_decode($sample->getContents(), true);
                if ($list) {
                    if (isset($contents['items'])) {
                        $items = $contents['items'];
                    } else {
                        $items = $contents;
                    }
                    foreach ($items as $i => $item) {
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

    #[Test]
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
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('http://www.example.com/');

        return $urlGenerator;
    }

    final protected function stubAuthorizationChecker() : AuthorizationCheckerInterface
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')->willReturn(true);

        return $authorizationChecker;
    }
}
