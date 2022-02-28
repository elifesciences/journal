<?php

namespace eLife\Journal\GoogleClient;

use eLife\Journal\Etoc\Newsletter;
use Google\Service\Sheets;
use Google\Service\Sheets\AppendValuesResponse;
use Webmozart\Assert\Assert;

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

    public function record(array $reasons, $reasonOther, bool $optOut, Newsletter $newsletter = null) : AppendValuesResponse
    {
        Assert::true($optOut || $newsletter instanceof Newsletter, 'Opt-out must be true or Newsletter provided.');
        $this->sheets->getClient()->fetchAccessTokenWithRefreshToken($this->refreshToken);
        return $this->sheets->spreadsheets_values->append(
            $this->sheetId,
            'A1:H1',
            new Sheets\ValueRange(['values' => [
                array_merge(
                    [
                        $newsletter ? $newsletter->label() : '',
                        $optOut,
                    ],
                    array_map(function ($reason) use ($reasons) {
                        return in_array($reason, $reasons);
                    }, range(1, 5)),
                    [
                        $reasonOther,
                    ]
                ),
            ]]),
            [
                'valueInputOption' => 'RAW',
            ]
        );
    }
}
