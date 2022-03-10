<?php

namespace eLife\Journal\GoogleClient;

use Assert\Assertion;
use DateTimeImmutable;
use eLife\CiviContacts\Etoc\Newsletter;
use Google\Service\Sheets;
use Google\Service\Sheets\AppendValuesResponse;
use Psr\Log\LoggerInterface;

final class OptoutUnsubscribeReason implements OptoutUnsubscribeReasonInterface
{
    private $sheets;
    private $sheetId;
    private $refreshToken;

    public function __construct(Sheets $sheets, string $sheetId, string $refreshToken, LoggerInterface $logger)
    {
        $this->sheets = $sheets;
        $this->sheetId = $sheetId;
        $this->refreshToken = $refreshToken;

        $this->sheets->getClient()->setLogger($logger);
    }

    public function record(array $reasons, $reasonOther, bool $optOut, Newsletter $newsletter = null, DateTimeImmutable $datetime = null) : AppendValuesResponse
    {
        Assertion::true($optOut || $newsletter instanceof Newsletter, 'Opt-out must be true or Newsletter provided.');

        // Access token refresh.
        $this->sheets->getClient()->fetchAccessTokenWithRefreshToken($this->refreshToken);

        return $this->sheets->spreadsheets_values->append(
            $this->sheetId,
            'A1:I1',
            new Sheets\ValueRange(['values' => [
                array_merge(
                    [
                        $this->formatDate($datetime),
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
                'valueInputOption' => 'USER_ENTERED',
            ]
        );
    }

    private function formatDate(DateTimeImmutable $datetime = null, string $format = 'd/m/Y H:i:s') : string
    {
        return ($datetime ?? new DateTimeImmutable())->format($format);
    }
}
