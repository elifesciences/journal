<?php

namespace eLife\Journal\ViewModel\Builder;

use Assert\Assertion;
use eLife\Patterns\ViewModel;

final class PictureBuilder
{
    private $uriGenerator;
    private $altText;
    private $originalWidth;
    private $originalHeight;
    private $types = [
        'image/svg+xml' => false,
        'image/webp' => false,
        'image/png' => false,
        'image/jpeg' => false,
    ];
    private $sizes = [];

    public function __construct(callable $uriGenerator, string $altText = '')
    {
        $this->uriGenerator = $uriGenerator;
        $this->altText = $altText;
    }

    public function setOriginalSize(int $width, int $height) : PictureBuilder
    {
        Assertion::min($width, 1);
        Assertion::min($height, 1);

        $clone = clone $this;

        $clone->originalWidth = $width;
        $clone->originalHeight = $height;

        return $clone;
    }

    public function addType(string $type) : PictureBuilder
    {
        Assertion::keyExists($this->types, $type);

        $clone = clone $this;

        $clone->types[$type] = true;

        return $clone;
    }

    public function addSize(int $width, int $height = null, string $media = null) : PictureBuilder
    {
        Assertion::min($width, 1);
        Assertion::nullOrMin($height, 1);

        $clone = clone $this;

        $clone->sizes[] = compact('width', 'height', 'media');

        return $clone;
    }

    public function build() : ViewModel\Picture
    {
        $sources = [];

        $sizes = $this->sizes;
        $sizes = array_unique($sizes, SORT_REGULAR);

        usort($sizes, function (array $a, array $b) {
            if (null === $b['media']) {
                return 1;
            }

            if (null === $a['media']) {
                return -1;
            }

            return 1;
        });

        foreach (array_reverse($sizes) as $size) {
            foreach (array_keys(array_filter($this->types)) as $type) {
                if ('image/svg+xml' === $type) {
                    $sources[] = array_filter([
                        'srcset' => call_user_func($this->uriGenerator, $type, $size['width'], $size['height']),
                        'type' => $type,
                        'media' => $size['media'] ?? null,
                    ]);

                    continue;
                }

                $srcset = implode(', ', array_filter(array_map(function (int $scale) use ($size, $type) {
                    $width = $size['width'] * $scale;
                    $height = $size['height'] * $scale;

                    if ($this->originalWidth && $width > $this->originalWidth) {
                        if ($this->originalWidth > $size['width']) {
                            if ($height) {
                                $ratio = $height / $width;
                                $height = $this->originalWidth * $ratio;
                            }
                            $width = $this->originalWidth;
                        } else {
                            return null;
                        }
                    }

                    $uri = call_user_func($this->uriGenerator, $type, $width, $height);

                    return "{$uri} {$width}w";
                }, [2, 1])));

                if (empty($srcset)) {
                    continue;
                }

                $sources[] = array_filter([
                    'srcset' => $srcset,
                    'type' => $type,
                    'media' => $size['media'] ?? null,
                ]);
            }
        }

        $defaultPath = call_user_func($this->uriGenerator);

        if (empty($sources)) {
            foreach (array_keys(array_filter($this->types)) as $type) {
                $typePath = call_user_func($this->uriGenerator, $type);

                if ($typePath === $defaultPath) {
                    continue;
                }

                $sources[] = array_filter([
                    'srcset' => $typePath,
                    'type' => $type,
                ]);
            }
        }

        return new ViewModel\Picture(
            $sources,
            new ViewModel\Image($defaultPath, [], $this->altText)
        );
    }
}
