<?php

namespace eLife\Journal\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

final class OAuthUser implements UserInterface
{
    private $username;
    private $roles;

    public function __construct(string $username, array $roles)
    {
        $this->username = $username;
        $this->roles = $roles;
    }

    public function getRoles() : array
    {
        return $this->roles;
    }

    public function getPassword() : string
    {
        return '';
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername() : string
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
        // Do nothing.
    }
}
