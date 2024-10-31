<?php

use App\Http\Controllers\GoogleController;
use App\Http\Controllers\SheetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('login', function () {
    return 'login';
})->name('login');
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
Route::get('/list-google-sheets', [GoogleController::class, 'listGoogleSheets'])->name('listGoogleSheets');
Route::get('/read-sheet/{spreadsheetId}', [SheetController::class, 'readSheetData'])
    ->name('readSheetData');
Route::post('sheets-store/{spreadsheetId}/{sheetName}', [SheetController::class, 'storeSheetData'])->name('sheets.store');
Route::get('sheets-edit/{spreadsheetId}/{sheetName}/{rowIndex}', [SheetController::class, 'editSheetData'])->name('sheets.edit');
Route::put('sheets-update/{spreadsheetId}/{sheetName}/{rowIndex}', [SheetController::class, 'updateSheetData'])->name('sheets.update');
Route::delete('sheets-destroy/{spreadsheetId}/{sheetName}/{rowIndex}', [SheetController::class, 'deleteSheetData'])->name('sheets.delete');
