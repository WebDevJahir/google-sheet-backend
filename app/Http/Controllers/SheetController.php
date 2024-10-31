<?php

namespace App\Http\Controllers;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Sheets;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;

class SheetController extends Controller
{
    private function getGoogleSheetsService($request)
    {

        $bearerToken = $request->header('Authorization');
        $accessToken = substr($bearerToken, 7);

        if (!$accessToken) {
            return response()->json(['message' => 'Invalid authentication credentials.'], 401);
        }

        $client = new Client();
        $client->setAccessToken($accessToken);

        return new Sheets($client);
    }

    public function readSheetData(Request $request, $spreadsheetId): JsonResponse
    {
        try {
            $sheetsService = $this->getGoogleSheetsService($request);
            if ($sheetsService instanceof \Illuminate\Http\RedirectResponse) {
                return $sheetsService;
            }

            $spreadsheet = $sheetsService->spreadsheets->get($spreadsheetId);
            $sheets = $spreadsheet->getSheets();

            $allData = [];
            foreach ($sheets as $sheet) {
                $sheetName = $sheet->getProperties()->getTitle();
                $range = "{$sheetName}!A:Z";
                $response = $sheetsService->spreadsheets_values->get($spreadsheetId, $range);
                $values = $response->getValues() ?? [];
                $allData[$sheetName] = $values;
            }
            return response()->json($allData);
        } catch (\Google\Service\Exception $e) {
            if ($e->getCode() === 401) {
                return response()->json(['message' => 'Invalid authentication credentials.'], 401);
            } else {
                return response()->json(['message' => 'An error occurred while fetching Google Sheets.'], 500);
            }
        }
    }

    public function storeSheetData($spreadsheetId, $sheetName, Request $request): JsonResponse
    {
        try {
            $data = $request->request->all();
            $sheetsService = $this->getGoogleSheetsService($request);
            if ($sheetsService instanceof \Illuminate\Http\RedirectResponse) {
                return $sheetsService;
            }

            if (!is_array($data)) {
                return response()->json(['error' => 'Invalid data format.']);
            }

            $values = array_map(function ($value) {
                return $value ?? '';
            }, $data);

            $range = "{$sheetName}!A:Z";
            $response = $sheetsService->spreadsheets_values->get($spreadsheetId, $range);
            $existingValues = $response->getValues() ?? [];
            $startRow = count($existingValues) + 1;

            $appendRange = "{$sheetName}!A{$startRow}:Z";

            $body = new Sheets\ValueRange(['values' => [$values]]);

            $sheetsService->spreadsheets_values->append($spreadsheetId, $appendRange, $body, [
                'valueInputOption' => 'RAW'
            ]);

            return response()->json($values);
        } catch (\Google\Service\Exception $e) {
            if ($e->getCode() === 401) {
                return response()->json(['message' => 'Invalid authentication credentials.'], 401);
            } else {
                return response()->json(['message' => 'An error occurred while fetching Google Sheets.'], 500);
            }
        }
    }

    public function editSheetData($spreadsheetId, $sheetName, $rowIndex, Request $request): JsonResponse
    {
        try {
            $sheetsService = $this->getGoogleSheetsService($request);
            $range = "{$sheetName}!A{$rowIndex}:Z{$rowIndex}";
            $response = $sheetsService->spreadsheets_values->get($spreadsheetId, $range);
            $values = $response->getValues()[0] ?? [];
            return response()->json($values);
        } catch (\Google\Service\Exception $e) {
            if ($e->getCode() === 401) {
                return response()->json(['message' => 'Invalid authentication credentials.'], 401);
            } else {
                return response()->json(['message' => 'An error occurred while fetching Google Sheets.'], 500);
            }
        }
    }

    public function updateSheetData($spreadsheetId, $sheetName, $rowIndex, Request $request): JsonResponse
    {
        try {
            $sheetsService = $this->getGoogleSheetsService($request);

            if ($sheetsService instanceof \Illuminate\Http\RedirectResponse) {
                return $sheetsService;
            }

            $data = $request->request->all();
            $range = "{$sheetName}!A{$rowIndex}:Z{$rowIndex}";

            $values = [array_map(function ($value) {
                return $value ?? '';
            }, $data)];

            $body = new Sheets\ValueRange(['values' => $values]);
            $sheetsService->spreadsheets_values->update($spreadsheetId, $range, $body, [
                'valueInputOption' => 'RAW'
            ]);

            return response()->json(
                [
                    'message' => 'Data updated successfully!',
                    'data' => $values
                ]
            );
        } catch (\Google\Service\Exception $e) {
            if ($e->getCode() === 401) {
                return response()->json(['message' => 'Invalid authentication credentials.'], 401);
            } else {
                return response()->json(['message' => 'An error occurred while fetching Google Sheets.'], 500);
            }
        }
    }


    public function deleteSheetData($spreadsheetId, $sheetName, $rowIndex, Request $request): JsonResponse
    {
        try {
            $sheetsService = $this->getGoogleSheetsService($request);
            if ($sheetsService instanceof \Illuminate\Http\RedirectResponse) {
                return $sheetsService;
            }

            // Get sheet ID based on sheet name
            $spreadsheet = $sheetsService->spreadsheets->get($spreadsheetId);
            $sheetId = null;
            foreach ($spreadsheet->getSheets() as $sheet) {
                if ($sheet->getProperties()->getTitle() === $sheetName) {
                    $sheetId = $sheet->getProperties()->getSheetId();
                    break;
                }
            }

            if (is_null($sheetId)) {
                return response()->json(['message' => 'Sheet not found.'], 404);
            }

            // Create DeleteDimensionRequest to delete the row
            $deleteRequest = new Sheets\DeleteDimensionRequest([
                'range' => [
                    'sheetId' => $sheetId,
                    'dimension' => 'ROWS',
                    'startIndex' => $rowIndex - 1, // zero-based index
                    'endIndex' => $rowIndex
                ]
            ]);

            $batchUpdateRequest = new Sheets\BatchUpdateSpreadsheetRequest([
                'requests' => [
                    new Sheets\Request(['deleteDimension' => $deleteRequest])
                ]
            ]);

            // Execute the batch update
            $sheetsService->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);

            $data = [
                'spreadsheet_id' => $spreadsheetId,
                'sheet_name' => $sheetName,
                'row_index' => $rowIndex
            ];

            return response()->json([
                'message' => 'Row deleted successfully!',
                'data' => $data
            ]);
        } catch (\Google\Service\Exception $e) {
            if ($e->getCode() === 401) {
                return response()->json(['message' => 'Invalid authentication credentials.'], 401);
            } else {
                return response()->json(['message' => 'An error occurred while deleting the row.'], 500);
            }
        }
    }
}
