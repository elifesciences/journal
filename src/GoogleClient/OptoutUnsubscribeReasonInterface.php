<?php

namespace eLife\Journal\GoogleClient;

use eLife\Journal\Etoc\Newsletter;
use Google\Service\Sheets\AppendValuesResponse;

interface OptoutUnsubscribeReasonInterface
{
    public function record(array $reasons, $reasonOther, bool $optOut, Newsletter $newsletter = null) : AppendValuesResponse;
}
