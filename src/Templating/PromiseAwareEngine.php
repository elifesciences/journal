<?php

namespace eLife\Journal\Templating;

use function GuzzleHttp\Promise\all;
use Symfony\Component\Templating\EngineInterface;

final class PromiseAwareEngine implements EngineInterface
{
    private $engine;

    public function __construct(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    public function render($name, array $parameters = [])
    {
        return $this->engine->render($name, all($parameters)->wait());
    }

    public function exists($name)
    {
        return $this->engine->exists($name);
    }

    public function supports($name)
    {
        return $this->engine->supports($name);
    }
}
