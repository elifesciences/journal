<?php

namespace eLife\Journal\GoogleClient;

use Google\Service\Sheets;
use Google\Service\Sheets\AppendValuesResponse;

final class OptoutUnsubscribeReason implements OptoutUnsubscribeReasonInterface
{
    private $sheets;
    private $sheetId;
    private $refreshToken;

    public function __construct(Sheets $sheets, string $sheetId, string $refreshToken)
    {
        $this->sheets = $sheets;
        $this->sheetId = $sheetId;
        $this->refreshToken = $refreshToken;
    }

    public function record(array $reasons, $reasonOther, bool $optOut, string $newsletter = null) : AppendValuesResponse
    {
        $this->sheets->getClient()->fetchAccessTokenWithRefreshToken($this->refreshToken);
        return $this->sheets->spreadsheets_values->append(
            $this->sheetId,
            'A1:B1',
            new Sheets\ValueRange(['values' => [
                ['one', 'two'],
                ['three', 'four'],
            ]])
        );
    }
}
