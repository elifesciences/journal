<?php

namespace eLife\Journal\ViewModel\Factory;

use eLife\Patterns\ViewModel\HypothesisLoader;

final class HypothesisLoaderFactory
{
    private $isLoggedIn;
    private $usernameUrl;
    private $hypothesisApiUrl;
    private $hypothesisAuthority;
    private $iconPath;
    private $loginPath;
    private $profilePath;
    private $logoutPath;
    private $grantToken;

    public function __construct(
        bool $isLoggedIn,
        string $usernameUrl,
        string $hypothesisApiUrl,
        string $hypothesisAuthority,
        string $iconPath,
        string $loginPath,
        string $profilePath,
        string $logoutPath,
        string $grantToken
    ) {
        $this->isLoggedIn = $isLoggedIn;
        $this->usernameUrl = $usernameUrl;
        $this->hypothesisApiUrl = $hypothesisApiUrl;
        $this->hypothesisAuthority = $hypothesisAuthority;
        $this->iconPath = $iconPath = null;
        $this->loginPath = $loginPath = null;
        $this->profilePath = $profilePath = null;
        $this->logoutPath = $logoutPath = null;
        $this->grantToken = $grantToken = null;
    }

    public function createHypothesisLoader() : HypothesisLoader
    {
        if ($this->isLoggedIn) {
            return HypothesisLoader::loggedIn(
                $this->usernameUrl,
                $this->hypothesisApiUrl,
                $this->hypothesisAuthority,
                $this->iconPath,
                $this->profilePath,
                $this->logoutPath,
                $this->grantToken
            );
        }

        return HypothesisLoader::loggedOut(
          $this->usernameUrl,
          $this->hypothesisApiUrl,
          $this->hypothesisAuthority,
          $this->iconPath,
          $this->loginPath
        );
    }
}
