<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class AudioPlayer implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $episodeNumber;
    private $title;
    private $url;
    /** @var MediaSource[] */
    private $sources;
    private $metadata;

    public function __construct(int $episodeNumber, Link $title, array $sources, array $chapters)
    {
        Assertion::min($episodeNumber, 1);
        Assertion::allIsInstanceOf($sources, MediaSource::class);
        Assertion::notEmpty($chapters);
        Assertion::allIsInstanceOf($chapters, MediaChapterListingItem::class);
        Assertion::allTrue(array_map(function (MediaSource $mediaSource) {
            return 0 === strpos($mediaSource['mediaType']['forMachine'], 'audio');
        }, $sources), 'All sources must be audio types.');

        $this->episodeNumber = $episodeNumber;
        $this->title = $title['name'];
        $this->url = $title['url'];
        $this->sources = array_map(function (MediaSource $source) {
            if (empty($source['fallback'])) {
                return $source;
            }

            $fallback = $source['fallback']->toArray();
            $fallback['classes'] = 'media-source__fallback_link--audio-player';

            return FlexibleViewModel::fromViewModel($source)->withProperty('fallback', $fallback);
        }, $sources);
        $this->metadata = [
            'number' => $episodeNumber,
            'chapters' => [],
        ];
        foreach ($chapters as $chapter) {
            $this->metadata['chapters'][] = [
                'number' => $chapter['chapterNumber'],
                'title' => $chapter['title'],
                'time' => $chapter['startTime']['forMachine'],
            ];
        }
        $this->metadata = str_replace('"', '\'', json_encode($this->metadata));
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/audio-player.mustache';
    }
}
