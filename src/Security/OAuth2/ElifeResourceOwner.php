<?php

namespace eLife\Journal\Security\OAuth2;

use Http\Client\Exception\HttpException;
use HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class ElifeResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        $response = $this->getUserResponse();
        $response->setData($accessToken);

        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetTokenRequest($url, array $parameters = [])
    {
        try {
            return $this->httpRequest(
                $url,
                http_build_query($parameters, '', '&'),
                ['Content-Type' => 'application/x-www-form-urlencoded'],
                'POST'
            );
        } catch (HttpTransportException $e) {
            $previous = $e->getPrevious();
            if (false === $previous instanceof HttpException) {
                throw $e;
            }

            if (false !== strpos($previous->getResponse()->getBody(), 'No name visible')) {
                throw new AuthenticationException('No name visible', 0, $e);
            }

            throw $e;
        }
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->remove('infos_url');
    }
}
