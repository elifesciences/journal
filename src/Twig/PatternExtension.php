<?php

namespace eLife\Journal\Twig;

use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use Twig_Extension;
use Twig_SimpleFunction;

final class PatternExtension extends Twig_Extension
{
    private $renderer;

    public function __construct(PatternRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'render_pattern',
                [$this, 'renderPattern'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function renderPattern(ViewModel $viewModel) : string
    {
        return $this->renderer->render($viewModel);
    }

    public function getName()
    {
        return 'pattern';
    }
}
