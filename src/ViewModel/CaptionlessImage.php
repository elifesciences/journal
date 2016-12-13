<?php

namespace eLife\Journal\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\SimplifyAssets;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Image;
use eLife\Patterns\ViewModel\IsCaptioned;
use eLife\Patterns\ViewModel\Picture;
use InvalidArgumentException;
use Traversable;

final class CaptionlessImage implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;
    use SimplifyAssets;

    private $picture;
    private $altText;
    private $defaultPath;
    private $srcset;

    public function __construct(IsCaptioned $image)
    {
        if ($image instanceof Image) {
            $this->altText = $image['altText'];
            $this->defaultPath = $image['defaultPath'];
            $this->srcset = $image['srcset'];
        } elseif ($image instanceof Picture) {
            $this->picture = $image;
        } else {
            throw new InvalidArgumentException('Unknown image type '.get_class($image));
        }
    }

    public function getTemplateName() : string
    {
        return '/elife/journal/patterns/captionless-image.mustache';
    }

    protected function getComposedViewModels() : Traversable
    {
        yield $this->picture;
    }
}
