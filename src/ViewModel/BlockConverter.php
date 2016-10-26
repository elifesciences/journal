<?php

namespace eLife\Journal\ViewModel;

use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\AssetViewerInline;
use eLife\Patterns\ViewModel\CaptionedAsset;
use eLife\Patterns\ViewModel\Doi;
use eLife\Patterns\ViewModel\IFrame;
use eLife\Patterns\ViewModel\Image;
use eLife\Patterns\ViewModel\IsCaptioned;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\MediaSource;
use eLife\Patterns\ViewModel\MediaType;
use eLife\Patterns\ViewModel\PullQuote;
use eLife\Patterns\ViewModel\Table as TableViewModel;
use eLife\Patterns\ViewModel\Video;
use Traversable;
use UnexpectedValueException;

final class BlockConverter
{
    private $patternRenderer;

    public function __construct(PatternRenderer $patternRenderer)
    {
        $this->patternRenderer = $patternRenderer;
    }

    public function handleBlocks(array ...$blocks) : Traversable
    {
        return $this->handleLevelledBlocks($blocks);
    }

    public function handleLevelledBlocks(array $blocks, int $level = 1) : Traversable
    {
        $isFirst = true;

        foreach ($blocks as $block) {
            yield $this->handleBlock($block, $level, $isFirst);

            $isFirst = false;
        }
    }

    private function handleBlock(array $block, int $level, bool $isFirst = false) : ViewModel
    {
        switch ($type = $block['type'] ?? 'no type') {
            case 'image':
                $image = new Image($block['uri'], [], $block['alt']);

                if (empty($block['title'])) {
                    return new CaptionlessImage($image);
                }

                return $this->createCaptionedAsset($image, $block);
            case 'paragraph':
                return new Paragraph($block['text']);
            case 'question':
                return ArticleSection::basic(
                    $block['question'],
                    $level + 1,
                    $this->renderViewModels($this->handleLevelledBlocks($block['answer'], $level + 1)),
                    null,
                    null,
                    $isFirst
                );
            case 'quote':
                return new PullQuote($this->renderViewModels($this->handleBlocks(...$block['text'])),
                    $block['cite'] ?? null, false);
            case 'section':
                return ArticleSection::basic(
                    $block['title'],
                    $level + 1,
                    $this->renderViewModels($this->handleLevelledBlocks($block['content'], $level + 1)),
                    $block['id'] ?? null,
                    null,
                    $isFirst
                );
            case 'table':
                if (empty($block['title'])) {
                    return new Table(implode('', $block['tables']),
                        $this->renderViewModels($this->handleBlocks(...$block['footer'] ?? [])));
                }

                $table = new TableViewModel(...$block['tables']);

                return $this->createCaptionedAsset($table, $block);
            case 'youtube':
                return new IFrame('https://www.youtube.com/embed/'.$block['id'], $block['width'], $block['height']);
            case 'video':
                $video = new Video($block['image'], array_map(function (array $source) {
                    return new MediaSource($source['uri'], new MediaType($source['mediaType']));
                }, $block['sources']));

                if (empty($block['title'])) {
                    return $video;
                }

                return $this->createCaptionedAsset($video, $block);
            default:
                throw new UnexpectedValueException('Unknown block type'.$type);
        }
    }

    private function createCaptionedAsset(IsCaptioned $asset, array $block) : ViewModel
    {
        $doi = !empty($block['doi']) ? new Doi($block['doi']) : null;
        if (!empty($block['uri'])) {
            $download = !empty($block['doi']) ? new Link('Download', $block['uri']) : null;
        } else {
            $download = null;
        }

        if (empty($block['caption'])) {
            $asset = CaptionedAsset::withOnlyHeading($asset, $block['title'], $doi, $download);
        } else {
            $asset = CaptionedAsset::withParagraphs($asset, $block['title'],
                array_map(function (array $block) {
                    if ('mathml' === $block['type']) {
                        return $block['mathml'];
                    }

                    return $block['text'];
                }, $block['caption']), $doi, $download);
        }

        if (empty($block['label'])) {
            return $asset;
        }

        return AssetViewerInline::primary($block['id'], $block['label'], $asset);
    }

    private function renderViewModels(Traversable $viewModels) : string
    {
        $return = '';

        foreach ($viewModels as $viewModel) {
            $return .= $this->patternRenderer->render($viewModel);
        }

        return $return;
    }
}
