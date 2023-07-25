<?php

namespace eLife\Journal\Helper;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;

final class DoiVersion
{

    private $articleVersion;

    public function __construct(ArticleVersion $articleVersion)
    {
        $this->articleVersion = $articleVersion;
    }

    public function __toString()
    {
        if ($this->articleVersion instanceof ArticleVoR) {
            return $this->articleVersion->getDoiVersion() ?? $this->articleVersion->getDoi();
        } else {
            return $this->articleVersion->getDoi();
        }
    }
}
