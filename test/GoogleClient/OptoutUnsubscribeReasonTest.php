<?php

namespace test\eLife\Journal\GoogleClient;

use eLife\Journal\Etoc\LatestArticles;
use eLife\Journal\Etoc\Newsletter;
use eLife\Journal\GoogleClient\OptoutUnsubscribeReason;
use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\AppendValuesResponse;
use Google\Service\Sheets\Resource\SpreadsheetsValues;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Traversable;

final class OptoutUnsubscribeReasonTest extends TestCase
{
    /** @var Sheets */
    private $sheets;
    /** @var Client */
    private $client;
    private $sheetId = 'sheet-id';
    private $refreshToken = 'refresh-token';
    /** @var LoggerInterface */
    private $logger;
    /** @var OptoutUnsubscribeReason */
    private $optoutUnsubscribeReason;

    public function setUp()
    {
        $this->sheets = $this->createMock(Sheets::class);
        $this->client = $this->createMock(Client::class);
        $this->sheets->spreadsheets_values = $this->createMock(SpreadsheetsValues::class);
        $this->sheets->expects($this->any())->method('getClient')->willReturn($this->client);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->optoutUnsubscribeReason = new OptoutUnsubscribeReason($this->sheets, $this->sheetId, $this->refreshToken, $this->logger);
        $this->sheets->expects($this->any())->method('getClient')->willReturn($this->client);
    }

    /**
     * @test
     */
    public function it_will_fetch_an_access_token()
    {
        $this->sheets->spreadsheets_values->expects($this->once())->method('append')->willReturn($this->createMock(AppendValuesResponse::class));
        $this->client->expects($this->once())->method('fetchAccessTokenWithRefreshToken')->with($this->identicalTo($this->refreshToken));
        $this->optoutUnsubscribeReason->record([1, 3, 5], 'other', false, new LatestArticles());
    }

    /**
     * @test
     * @dataProvider recordProvider
     */
    public function it_will_append_values_to_google_sheet(
        array $reasons,
        string $reasonOther,
        bool $optOut,
        Newsletter $newsletter = null,
        array $expected
    )
    {
        $this->sheets->spreadsheets_values->expects($this->once())->method('append')->with(
            $this->sheetId,
            'A1:H1',
            new Sheets\ValueRange([
                'values' => [
                    $expected,
                ],
            ]),
            [
                'valueInputOption' => 'RAW',
            ]
        )->willReturn($this->createMock(AppendValuesResponse::class));
        $this->client->expects($this->once())->method('fetchAccessTokenWithRefreshToken');
        $this->optoutUnsubscribeReason->record($reasons, $reasonOther, $optOut, $newsletter);
    }

    public function recordProvider() : Traversable
    {
        yield 'unsubscribe latest_articles' => [
            [1, 2, 3],
            'other',
            false,
            new LatestArticles(),
            [
                'latest_articles',
                false,
                true,
                true,
                true,
                false,
                false,
                'other',
            ],
        ];
        yield 'opt-out' => [
            [1, 2, 3, 5],
            'other',
            true,
            null,
            [
                '',
                true,
                true,
                true,
                true,
                false,
                true,
                'other',
            ],
        ];
    }

    /**
     * @test
     */
    public function optout_or_newsletter_must_be_provided()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->optoutUnsubscribeReason->record([], '', false);
    }
}
