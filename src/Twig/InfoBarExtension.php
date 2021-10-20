<?php

namespace eLife\Journal\Twig;

use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\InfoBar;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class InfoBarExtension extends AbstractExtension
{
    private $renderer;

    public function __construct(PatternRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction(
                'info_bar',
                [$this, 'infoBar'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function infoBar(string $message, string $type = InfoBar::TYPE_INFO) : string
    {
        return $this->renderer->render(new InfoBar($message, $type));
    }
}
