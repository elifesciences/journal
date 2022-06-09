<?php

namespace eLife\Journal\GoogleClient;

use DateTimeImmutable;
use eLife\CiviContacts\Etoc\Newsletter;
use Google\Service\Sheets;

final class MockOptoutUnsubscribeReason implements OptoutUnsubscribeReasonInterface
{
    private $sheetId = 'MockSheetId';

    public function record(array $reasons, $reasonOther, bool $optOut, Newsletter $newsletter = null, DateTimeImmutable $datetime = null) : Sheets\AppendValuesResponse
    {
        $updateValuesResponse = new Sheets\UpdateValuesResponse();
        $updateValuesResponse->setSpreadsheetId($this->sheetId);
        $updateValuesResponse->setUpdatedCells(8);
        $updateValuesResponse->setUpdatedColumns(8);
        $updateValuesResponse->setUpdatedRange('Sheet1!A1:I1');
        $updateValuesResponse->setUpdatedRows(1);

        $response = new Sheets\AppendValuesResponse();
        $response->setSpreadsheetId($this->sheetId);
        $response->setTableRange('Sheet1!A1:I1');
        $response->setUpdates($updateValuesResponse);

        return $response;
    }
}
