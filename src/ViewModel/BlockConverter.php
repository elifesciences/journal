<?php

namespace eLife\Journal\ViewModel;

use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\CaptionedImage;
use eLife\Patterns\ViewModel\IFrame;
use eLife\Patterns\ViewModel\Image;
use eLife\Patterns\ViewModel\PullQuote;
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

                return CaptionedImage::withOnlyHeading($image, $block['title']);
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
                return new Table(implode('', $block['tables']),
                    $this->renderViewModels($this->handleBlocks(...$block['footer'] ?? [])));
            case 'youtube':
                return new IFrame('https://www.youtube.com/embed/'.$block['id'], $block['width'], $block['height']);
            default:
                throw new UnexpectedValueException('Unknown block type'.$type);
        }
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
