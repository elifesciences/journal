<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\PreprintReference;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class PreprintReferenceConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param PreprintReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $origin = [
            $object->getDate()->format(),
            $object->getSource(),
        ];

        return new ViewModel\Reference(
            $object->getArticleTitle(),
            implode('. ', $origin).'.',
            $object->getUri(),
            $object->getDoi() ? $object->getUri() : null,
            $this->createAuthors($object->getAuthors(), $object->authorsEtAl())
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PreprintReference;
    }
}
