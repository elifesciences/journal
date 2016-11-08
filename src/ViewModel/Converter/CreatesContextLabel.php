<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Subject;
use eLife\Patterns\ViewModel\ContextLabel;
use eLife\Patterns\ViewModel\Link;

trait CreatesContextLabel
{
    /**
     * @return ContextLabel|null
     */
    final private function createContextLabel($item)
    {
        if (!method_exists($item, 'getSubjects') || $item->getSubjects()->isEmpty()) {
            return null;
        }

        return new ContextLabel(...$item->getSubjects()->map(function (Subject $subject) {
            return new Link(
                $subject->getName(),
                $this->urlGenerator->generate('subject', ['id' => $subject->getId()])
            );
        }));
    }
}
