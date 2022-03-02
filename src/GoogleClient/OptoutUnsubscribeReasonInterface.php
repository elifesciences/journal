<?php

namespace eLife\Journal\GoogleClient;

use DateTimeImmutable;
use eLife\CiviContacts\Etoc\Newsletter;
use Google\Service\Sheets\AppendValuesResponse;

interface OptoutUnsubscribeReasonInterface
{
    public function record(array $reasons, $reasonOther, bool $optOut, Newsletter $newsletter = null, DateTimeImmutable $datetime = null) : AppendValuesResponse;
}
