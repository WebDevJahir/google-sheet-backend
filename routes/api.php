<?php

use App\Http\Controllers\GoogleController;
use App\Http\Controllers\SheetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/list-google-sheets', [GoogleController::class, 'listGoogleSheets'])->name('listGoogleSheets');
Route::get('/read-sheet/{spreadsheetId}', [SheetController::class, 'readSheetData'])
    ->name('readSheetData');
Route::post('sheets-store/{spreadsheetId}/{sheetName}', [SheetController::class, 'storeSheetData'])->name('sheets.store');
Route::get('sheets-edit/{spreadsheetId}/{sheetName}/{rowIndex}', [SheetController::class, 'editSheetData'])->name('sheets.edit');
Route::put('sheets-update/{spreadsheetId}/{sheetName}/{rowIndex}', [SheetController::class, 'updateSheetData'])->name('sheets.update');
Route::delete('sheets-destroy/{spreadsheetId}/{sheetName}/{rowIndex}', [SheetController::class, 'deleteSheetData'])->name('sheets.delete');
