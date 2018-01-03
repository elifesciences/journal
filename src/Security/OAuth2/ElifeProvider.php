<?php

namespace eLife\Journal\Security\OAuth2;

use BadMethodCallException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

final class ElifeProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    private $apiUrl;
    private $apiUrlPublic;

    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        $this->apiUrl = $options['api_url'];
        $this->apiUrlPublic = $options['api_url_public'] ?? $options['api_url'];
    }

    public function getBaseAuthorizationUrl() : string
    {
        return "{$this->apiUrlPublic}/oauth2/authorize";
    }

    public function getBaseAccessTokenUrl(array $params) : string
    {
        return "{$this->apiUrl}/oauth2/token";
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        throw new BadMethodCallException('No resource owner details URL');
    }

    protected function fetchResourceOwnerDetails(AccessToken $token) : array
    {
        return array_intersect_key($token->getValues(), array_flip(['id', 'orcid', 'name']));
    }

    protected function getDefaultScopes() : array
    {
        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            throw new IdentityProviderException($data['error_description'] ?? $data['error'], 0, (string) $response->getBody());
        }

        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException($response->getReasonPhrase(), 0, (string) $response->getBody());
        }
    }

    protected function getAuthorizationParameters(array $options) : array
    {
        $parameters = parent::getAuthorizationParameters($options);

        unset($parameters['approval_prompt']);

        $parameters = array_filter($parameters);

        ksort($parameters);

        return $parameters;
    }

    protected function createResourceOwner(array $response, AccessToken $token) : ElifeResourceOwner
    {
        return new ElifeResourceOwner($response['id'], $response['orcid'], $response['name']);
    }
}
