<?php

namespace eLife\Journal\GoogleClient;

use Google\Service\Sheets\AppendValuesResponse;

interface OptoutUnsubscribeReasonInterface
{
    public function record(array $reasons, $reasonOther, bool $optOut, string $newsletter = null) : AppendValuesResponse;
}
