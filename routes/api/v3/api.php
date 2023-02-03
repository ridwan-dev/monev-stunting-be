<?php

//use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Faker;
use \App\JsonResponse;
use \App\Constants;

use App\Http\Controllers\Api\V3\{
    KinerjaPembangunan\KinerjaPembangunanController,
    KrisnaIntegrasi\KrisnaRealisasiController,
    KinerjaAnggaran\RenjaV3Controller,
    Running\ShellController
};
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('ka/lokus-ro', [KinerjaPembangunanController::class, 'lokusRo']);
    Route::get('integrasi/realisasi-rka/{tahun}', [KrisnaRealisasiController::class, 'realisasiKomponen']);
    Route::get('t3rm1n4l', [ShellController::class, 'terminal']);
    
    Route::post('renja/renjakl-v3', [RenjaV3Controller::class, 'getKrisnaRenja']);
    

});