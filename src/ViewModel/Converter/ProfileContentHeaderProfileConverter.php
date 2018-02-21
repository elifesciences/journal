<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\AccessControl;
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
            ->filter(Callback::methodIsValue('getAccess', AccessControl::ACCESS_PUBLIC))
            ->map(Callback::method('getValue'))
            ->map(Callback::method('getName'))
            ->map(Callback::apply('end'))
            ->toArray());

        $emailAddress = $object->getEmailAddresses()
            ->filter(Callback::methodIsValue('getAccess', AccessControl::ACCESS_PUBLIC))
            ->map(Callback::method('getValue'))[0];

        $orcid = $object->getDetails()->getOrcid() ? new ViewModel\Orcid($object->getDetails()->getOrcid()) : null;

        if ($context['isUser'] ?? false) {
            return ViewModel\ContentHeaderProfile::loggedIn(
                $object->getDetails()->getPreferredName(),
                new ViewModel\Link('Log out', $this->urlGenerator->generate('log-out')),
                [
                    new ViewModel\Link('Manage my ORCID', 'https://orcid.org/my-orcid'),
                ],
                $affiliations,
                $emailAddress,
                $orcid
            );
        }

        return ViewModel\ContentHeaderProfile::notLoggedIn(
            $object->getDetails()->getPreferredName(),
            $affiliations,
            $emailAddress,
            $orcid
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Profile && ViewModel\ContentHeaderProfile::class === $viewModel;
    }
}
