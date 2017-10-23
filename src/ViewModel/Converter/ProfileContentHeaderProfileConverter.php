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

        return new ViewModel\ContentHeaderProfile(
            $object->getDetails()->getPreferredName(),
            ['Log out' => $this->urlGenerator->generate('log-out')],
            ['Manage profile' => 'https://orcid.org/my-orcid'],
            array_filter([
                'affiliations' => $affiliations,
                'emailAddress' => $emailAddress,
            ])
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Profile && ViewModel\ContentHeaderProfile::class === $viewModel;
    }
}
