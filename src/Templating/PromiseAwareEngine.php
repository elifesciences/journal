<?php

namespace eLife\Journal\Templating;

use Twig\Environment;
use GuzzleHttp\Promise\Utils;

final class PromiseAwareEngine
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function render($name, array $parameters = [])
    {
        return $this->twig->render($name, Utils::all($parameters)->wait());
    }
}
