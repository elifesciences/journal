<?php

namespace eLife\Journal\Security\OAuth2;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

final class ElifeResourceOwner implements ResourceOwnerInterface
{
    private $id;
    private $orcid;
    private $name;

    public function __construct(string $id, string $orcid, string $name)
    {
        $this->id = $id;
        $this->orcid = $orcid;
        $this->name = $name;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getOrcid() : string
    {
        return $this->orcid;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function toArray() : array
    {
        return [
            'id' => $this->id,
            'orcid' => $this->orcid,
            'name' => $this->name,
        ];
    }
}
