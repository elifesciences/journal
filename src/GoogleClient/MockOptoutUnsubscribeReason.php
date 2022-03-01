<?php

namespace eLife\Journal\GoogleClient;

use eLife\Journal\Etoc\Newsletter;
use Google\Service\Sheets;

final class MockOptoutUnsubscribeReason implements OptoutUnsubscribeReasonInterface
{
    private $sheetId = 'MockSheetId';

    public function record(array $reasons, $reasonOther, bool $optOut, Newsletter $newsletter = null) : Sheets\AppendValuesResponse
    {
        $updateValuesResponse = new Sheets\UpdateValuesResponse();
        $updateValuesResponse->setSpreadsheetId($this->sheetId);
        $updateValuesResponse->setUpdatedCells(7);
        $updateValuesResponse->setUpdatedColumns(7);
        $updateValuesResponse->setUpdatedRange('Sheet1!A1:H8');
        $updateValuesResponse->setUpdatedRows(1);

        $response = new Sheets\AppendValuesResponse();
        $response->setSpreadsheetId($this->sheetId);
        $response->setTableRange('Sheet1!A1:H8');
        $response->setUpdates($updateValuesResponse);

        return $response;
    }
}
