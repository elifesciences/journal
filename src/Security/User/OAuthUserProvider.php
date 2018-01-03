<?php

namespace eLife\Journal\Security\User;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class OAuthUserProvider implements UserProviderInterface
{
    private $roles;

    public function __construct(array $roles = ['ROLE_USER', 'ROLE_OAUTH_USER'])
    {
        $this->roles = $roles;
    }

    public function loadUserByUsername($username) : UserInterface
    {
        return new OAuthUser($username, $this->roles);
    }

    public function refreshUser(UserInterface $user) : UserInterface
    {
        if (!$user instanceof OAuthUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class) : bool
    {
        return OAuthUser::class === $class;
    }
}
