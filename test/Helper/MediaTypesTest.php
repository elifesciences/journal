<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\MediaTypes;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use test\eLife\Journal\Providers;
use Traversable;

final class MediaTypesTest extends TestCase
{
    use Providers;

    #[Test]
    #[DataProvider('extensionProvider')]
    public function it_provides_an_extension_for_a_media_type(string $mediaType, string $extension)
    {
        $this->assertSame($extension, MediaTypes::toExtension($mediaType));
    }

    public static function extensionProvider() : Traversable
    {
        return self::arrayProvider([
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/svg+xml' => 'svg',
            'image/webp' => 'webp',
        ]);
    }

    #[Test]
    public function it_fails_on_an_unknown_media_type()
    {
        $this->expectException(InvalidArgumentException::class);

        MediaTypes::toExtension('image/foo');
    }
}
