<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Profile;
use eLife\Journal\Helper\Callback;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ProfileContentHeaderProfileConverter implements ViewModelConverter
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Profile $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $affiliations = array_unique($object->getAffiliations()
            ->map(Callback::method('getName'))
            ->map(Callback::apply('end'))
            ->toArray());

        $emailAddress = $object->getEmailAddresses()[0];

        if ($context['isUser'] ?? false) {
            return ViewModel\ContentHeaderProfile::loggedIn(
                $object->getDetails()->getPreferredName(),
                new ViewModel\Link('Log out', $this->urlGenerator->generate('log-out')),
                [
                    new ViewModel\Link('Manage my ORCID', 'https://orcid.org/my-orcid'),
                ],
                $affiliations,
                $emailAddress
            );
        }

        return ViewModel\ContentHeaderProfile::notLoggedIn(
            $object->getDetails()->getPreferredName(),
            $affiliations,
            $emailAddress
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Profile && ViewModel\ContentHeaderProfile::class === $viewModel;
    }
}
