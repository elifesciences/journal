<?php

namespace eLife\Journal\ViewModel\Builder;

use Assert\Assertion;
use eLife\Patterns\ViewModel;

final class PictureBuilder
{
    private $uriGenerator;
    private $altText;
    private $types = [
        'image/svg+xml' => false,
        'image/png' => false,
        'image/jpeg' => false,
    ];
    private $sizes = [];

    public function __construct(callable $uriGenerator, string $altText = '')
    {
        $this->uriGenerator = $uriGenerator;
        $this->altText = $altText;
    }

    public function addType(string $type) : PictureBuilder
    {
        Assertion::keyExists($this->types, $type);

        $clone = clone $this;

        $clone->types[] = $type;

        return $clone;
    }

    public function addSize(int $width, int $height = null, string $media = null) : PictureBuilder
    {
        $clone = clone $this;

        $clone->sizes[] = compact('width', 'height', 'media');

        return $clone;
    }

    public function build() : ViewModel\Picture
    {
        $sources = [];

        foreach ($this->sizes as $size) {
            foreach (array_filter($this->types) as $type) {
                if ('image/svg+xml' === $type) {
                    $sources[] = array_filter([
                        'srcset' => call_user_func($this->uriGenerator, $type, $size['width'], $size['height']),
                        'type' => $type,
                        'media' => $size['media'] ?? null,
                    ]);

                    continue;
                }

                $srcset = implode(', ', array_map(function (int $scale) use ($size, $type) {
                    $width = $size['width'] * $scale;
                    $height = $size['height'] * $scale;

                    $uri = call_user_func($this->uriGenerator, $type, $width, $height);

                    return "{$uri} {$width}w";
                }, [2, 1]));

                $sources[] = array_filter([
                    'srcset' => $srcset,
                    'type' => $type,
                    'media' => $size['media'] ?? null,
                ]);
            }
        }

        return new ViewModel\Picture(
            $sources,
            new ViewModel\Image(call_user_func($this->uriGenerator), [], $this->altText)
        );
    }
}
