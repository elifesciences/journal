<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class ContentHeaderProfile implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $details;
    private $displayName;
    private $secondaryLinks;
    private $logoutLink;

    private function __construct()
    {
    }

    public static function loggedIn(
        string $displayName,
        Link $logoutLink = null,
        array $secondaryLinks = [],
        array $affiliations = [],
        string $emailAddress = null,
        Orcid $orcid = null
    ) : ContentHeaderProfile {
        Assertion::notEmpty($displayName);
        Assertion::allIsInstanceOf($secondaryLinks, Link::class);

        $contentHeader = new static();
        $contentHeader->displayName = $displayName;
        $contentHeader->details = $contentHeader->createDetails($affiliations, $emailAddress, $orcid);
        $contentHeader->logoutLink = $logoutLink;
        $contentHeader->secondaryLinks = $secondaryLinks;

        return $contentHeader;
    }

    public static function notLoggedIn(
        string $displayName,
        array $affiliations = [],
        string $emailAddress = null,
        Orcid $orcid = null
    ) : ContentHeaderProfile {
        Assertion::notEmpty($displayName);

        $contentHeader = new static();
        $contentHeader->displayName = $displayName;
        $contentHeader->details = $contentHeader->createDetails($affiliations, $emailAddress, $orcid);
        $contentHeader->_orcid = $orcid;

        return $contentHeader;
    }

    private function createDetails(array $affiliations = [], string $emailAddress = null, Orcid $orcid = null)
    {
        $details = [];

        if (!empty($affiliations)) {
            $details['affiliations'] = $affiliations;
        }

        if ($emailAddress) {
            $details['emailAddress'] = $emailAddress;
        }

        if ($orcid) {
            $details['orcid'] = $orcid;
        }

        if (!empty($details)) {
            return $details;
        }

        return null;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/content-header-profile.mustache';
    }
}
