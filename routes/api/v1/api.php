<?php

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Faker;
use \App\JsonResponse;
use \App\Constants;
use App\Http\Controllers\Api\V1\{
    Auth\AuthController,
    Ref\LocationController,
    Ref\DataController,
    Tracking\TrackingController,
    Tagging\TaggingController,
    KinerjaAnggaranPembangunan\KinerjaAnggaranController,
    KinerjaAnggaranPembangunan\PerkembanganPenandaanController,
    KinerjaAnggaranPembangunan\IndikasiKonvergensiImplementasiController,
    KinerjaAnggaranPembangunan\AnalisisKinerjaController,
    Interkoneksi\InterkoneksiEmonevController,
    Interkoneksi\InterkoneksiKrisnaDakController,
    Interkoneksi\InterkoneksiRkaklController,
    User\UserController,
    Dak\DakController,
    Sys\JsonCollectionController,
    Geodata\GeodataController,
    Dampak\IndikatorStuntingController,
    Monitoring\MonitoringController,
    Monitoring\RenjaController
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
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('user/registrasi', [UserController::class, 'registrasi']);
Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);
Route::post('auth/email-confirmation', [AuthController::class, 'emailConfirmation']);


Route::post('geom/kabupaten',[GeodataController::class,'kabupatenpublik']);
Route::post('geom/provinsi',[GeodataController::class,'provinsipublik']);
Route::post('geom/kecamatan',[GeodataController::class,'kecamatanpublik']);


Route::group(['middleware' => ['auth:sanctum']], function () {
    // Auth routes
    Route::get('auth/user', [AuthController::class, 'user']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/change-password', [AuthController::class, 'changePassword']);

    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });

    Route::get('user/me', [UserController::class, 'getMe']);
    Route::put('user/me', [UserController::class, 'putMe']);

    Route::get('user/list-registrasi', [UserController::class, 'listRegisteredUser']);
    Route::put('user/update-status-aktif', [Usercontroller::class]);

    Route::get('auth/ping', [AuthController::class, 'ping']);

    Route::get('sys/json-collection', [JsonCollectionController::class, 'index']);
    Route::get('sys/json-collection/{hashId}', [JsonCollectionController::class, 'getByHash']);

    // // Api resource routes
    // Route::apiResource('roles', 'RoleController')->middleware('permission:' . Constants::PERMISSION_PERMISSION_MANAGE);
    // Route::apiResource('users', 'UserController')->middleware('permission:' . Constants::PERMISSION_USER_MANAGE);
    // Route::apiResource('permissions', 'PermissionController')->middleware('permission:' . Constants::PERMISSION_PERMISSION_MANAGE);
    
    // Route::get('auth/ping', 'AuthController@ping')->middleware('permission:' . Constants::PERMISSION_USER_MANAGE);

    // // Custom routes
    // Route::put('users/{user}', 'UserController@update');
    // Route::get('users/{user}/permissions', 'UserController@permissions')->middleware('permission:' . Constants::PERMISSION_PERMISSION_MANAGE);
    // Route::put('users/{user}/permissions', 'UserController@updatePermissions')->middleware('permission:' .Constants::PERMISSION_PERMISSION_MANAGE);
    // Route::get('roles/{role}/permissions', 'RoleController@permissions')->middleware('permission:' . Constants::PERMISSION_PERMISSION_MANAGE);

    Route::post('tracking/realisasi-kegiatan', [TrackingController::class, 'index']);
    Route::get('tracking/realisasi-kegiatan/tahun', [TrackingController::class, 'tahun']);
    Route::post('tracking/realisasi-kegiatan/group/intervensi', [TrackingController::class, 'byIntervensi']);
    Route::post('tracking/realisasi-kegiatan/group/kementerian', [TrackingController::class, 'byKementerian']);
    Route::post('tracking/realisasi-kegiatan/group/tahun', [TrackingController::class, 'byTahun']);

    Route::get('data/kementerian', [DataController::class, 'kementerian']);
    Route::get('data/kementeriantesting', [DataController::class, 'kementerian']);
    Route::get('data/intervensi', [DataController::class, 'intervensi']);
    Route::get('data/intervensi/group', [DataController::class, 'groupIntervensi']);
    Route::get('data/tematik', [DataController::class, 'tematik']);
    Route::get('data/lokasi', [DataController::class, 'lokasi']);
    Route::get('data/menu', [DataController::class, 'menu']);
    Route::get('data/tahun', [DataController::class, 'tahun']);
    Route::get('data/program', [DataController::class, 'program']);
    Route::post('data/intervensipost', [DataController::class, 'intervensipost']);
    Route::post('data/intervensidelete', [DataController::class, 'intervensidelete']);

    Route::get('data/keywords', [DataController::class, 'keywords']);
    Route::get('data/satuan-komponen', [DataController::class, 'satuanKomponen']);

    Route::get('tagging/tahun', [TaggingController::class, 'getTahun']);
    Route::get('tagging/kementerian', [TaggingController::class, 'getKementerian']);
    Route::get('tagging/dashboard/tile', [TaggingController::class, 'getDashboardTile']);
    Route::get('tagging/dashboard/peta', [TaggingController::class, 'getPeta']);
    Route::get('tagging/dashboard/peta/{lokasiId}', [TaggingController::class, 'getPetaDetail']);

    Route::get('ka/tahun-semester', [KinerjaAnggaranController::class, 'tahunSemester']);
    Route::get('ka/kementerian', [KinerjaAnggaranController::class, 'kementerian']);
    Route::post('ka/renja/kementerian', [RenjaController::class, 'optionkementerian']);
    Route::get('ka/intervensi', [KinerjaAnggaranController::class, 'intervensi']);
    Route::post('ka/kinerja-anggaran', [KinerjaAnggaranController::class, 'getKinerjaAnggaran']);
    Route::post('ka/ro-capaian', [KinerjaAnggaranController::class, 'getDetailRoCapaian']);
    Route::post('ka/ro-anggaran', [KinerjaAnggaranController::class, 'getDetailRoAnggaran']);
    Route::post('ka/ro-intervensi', [KinerjaAnggaranController::class, 'getDetailRoIntervensi']);
    Route::post('ka/chart-1', [KinerjaAnggaranController::class, 'chart1']);
    Route::post('ka/chart-1-hap', [KinerjaAnggaranController::class, 'chart1Hap']);

    Route::get('pp/tahun-semester', [PerkembanganPenandaanController::class, 'tahunSemester']);
    Route::get('pp/kementerian', [PerkembanganPenandaanController::class, 'kementerian']);
    Route::get('pp/intervensi', [PerkembanganPenandaanController::class, 'intervensi']);
    Route::post('pp/perkembangan-penandaan', [PerkembanganPenandaanController::class, 'getPerkembanganPenandaan']);
    Route::post('pp/perkembangan-penandaan-2', [PerkembanganPenandaanController::class, 'getPerkembanganPenandaan2']);
    Route::post('pp/chart-1', [PerkembanganPenandaanController::class, 'chart1']);
    Route::post('pp/chart-1-hap', [PerkembanganPenandaanController::class, 'chart1Hap']);
    Route::post('pp/ro-penandaan', [PerkembanganPenandaanController::class, 'getRoPerkembanganPenandaan']);
    Route::post('pp/ro-penandaan-kesepakatan', [PerkembanganPenandaanController::class, 'kesepakatanRoPerkembanganPenandaan']);
    Route::post('pp/ro-kesepakatan-publish', [PerkembanganPenandaanController::class, 'kesepakatanRoPublish']);
    Route::get('pp/krisna-update', [PerkembanganPenandaanController::class, 'getKrisnaUpdate']);
    
    Route::post('pp/ro-revisi', [PerkembanganPenandaanController::class, 'rorevisi']);

    Route::get('iki/tahun-semester', [IndikasiKonvergensiImplementasiController::class, 'tahunSemester']);
    Route::get('iki/kementerian', [IndikasiKonvergensiImplementasiController::class, 'kementerian']);
    Route::get('iki/intervensi', [IndikasiKonvergensiImplementasiController::class, 'intervensi']);
    Route::post('iki/indikasi-konvergensi-implementasi', [IndikasiKonvergensiImplementasiController::class, 'getData']);
    Route::post('iki/chart-1', [IndikasiKonvergensiImplementasiController::class, 'chart1']);
    Route::post('iki/chart-2', [IndikasiKonvergensiImplementasiController::class, 'chart2']);
    Route::post('iki/chart-3', [IndikasiKonvergensiImplementasiController::class, 'chart3']);
    Route::post('iki/chart-4', [IndikasiKonvergensiImplementasiController::class, 'chart4']);
    Route::post('iki/chart-5', [IndikasiKonvergensiImplementasiController::class, 'chart5']);

    Route::get('ak/tahun-semester', [AnalisisKinerjaController::class, 'tahunSemester']);
    Route::get('ak/kementerian', [AnalisisKinerjaController::class, 'kementerian']);
    Route::get('ak/intervensi', [AnalisisKinerjaController::class, 'intervensi']);
    Route::post('ak/analisis-kinerja', [AnalisisKinerjaController::class, 'getData']);

    Route::get('dak/tahun', [DakController::class, 'tahun']);
    Route::get('dak/kementerian', [DakController::class, 'kementerian']);
    Route::get('dak/bidang', [DakController::class, 'bidang']);
    Route::post('dak/data-by-bidang', [DakController::class, 'dataByBidang']);
    Route::post('dak/data-by-tematik', [DakController::class, 'dataByTematik']);
    Route::post('dak/data-by-prov-pemda', [DakController::class, 'dataByProvinsiPemda']);
    Route::post('dak/data-by-kemen-tingpel', [DakController::class, 'dataByKementerianTingpel']);
    Route::post('dak/data-by-tingpel', [DakController::class, 'dataByTingpel']);

    Route::post('dak/total', [DakController::class, 'dataTotalDak']);

    Route::post('dak/one-page', [DakController::class, 'getOnePage']);

    Route::post('dak/data-by-tahun', [DakController::class, 'dataByTahun']);
    
    Route::post('dak/data-peta', [DakController::class, 'getPetaDak']);

    Route::post('geodata/provinsi', [GeodataController::class, 'provinsi']);
    Route::post('geodata/kabupaten/{id?}', [GeodataController::class, 'kabupaten']);
    Route::post('geodata/agg/kabupaten/{id?}', [GeodataController::class, 'aggKabupaten']);
    Route::post('geodata/monitoring', [GeodataController::class, 'dataMonitoring']);
    Route::post('geodata/stunting', [GeodataController::class, 'dataStunting']);
    Route::post('geodata/stuntingkab', [GeodataController::class, 'dataStuntingKab']);
    Route::post('geodata/stuntingkabtest', [GeodataController::class, 'dataStuntingKab']);
    Route::post('geodata/wasting', [GeodataController::class, 'dataWasting']);
    Route::post('geodata/wastingkab', [GeodataController::class, 'dataWastingKab']);
    Route::post('geodata/underweight', [GeodataController::class, 'dataUnderweight']);
    Route::post('geodata/underweightkab', [GeodataController::class, 'dataUnderweightKab']);


    Route::post('geodata/tesprioritas', [GeodataController::class, 'tesprioritas']);


    Route::post('monitoring/capaian/', [MonitoringController::class, 'capaian']);
    Route::get('monitoring/capaian/detail', [MonitoringController::class, 'capaianDetail']);
    Route::get('monitoring/capaian/detail/lokasi', [MonitoringController::class, 'capaianDetailByLokasi']);
    Route::get('monitoring/capaian/indikator', [MonitoringController::class, 'capaianIndikator']);
    Route::post('monitoring/capaian/intervensi', [MonitoringController::class, 'intervensiPage']);
    Route::post('monitoring/capaian/intervensitotal', [MonitoringController::class, 'intervensiTotal']);


    Route::post('renja/capaian/', [RenjaController::class, 'capaian']);
    Route::get('renja/capaian/detail', [RenjaController::class, 'capaianDetail']);
    Route::get('renja/capaian/detail/lokasi', [RenjaController::class, 'capaianDetailByLokasi']);
    Route::get('renja/capaian/indikator', [RenjaController::class, 'capaianIndikator']);
    Route::post('renja/capaian/intervensi', [RenjaController::class, 'intervensiPage']);
    Route::post('renja/capaian/intervensitotal', [RenjaController::class, 'intervensiTotal']);
    Route::post('renja/renjakl', [RenjaController::class, 'getKrisnaRenja']);
    Route::post('renja/renjalokus', [RenjaController::class, 'getKrisnaRenjaLokus']);
    Route::post('renja/kabupaten', [RenjaController::class, 'kabupaten']);
    Route::post('renja/provinsi', [RenjaController::class, 'provinsi']);
    Route::post('renja/kementerian', [RenjaController::class, 'kementerian']);
    Route::post('renja/tahun', [RenjaController::class, 'tahun']);
    Route::post('renja/taggingpost', [RenjaController::class, 'renjatagging']);
    Route::post('renja/listro', [RenjaController::class, 'listro']);
    Route::post('renja/listro-tagging', [RenjaController::class, 'listroTagging']);
    Route::post('renja/rointervensi', [RenjaController::class, 'rointervensi']);
    Route::post('renja/rokeyword', [RenjaController::class, 'rokeyword']);
    Route::get('renja/keyword-reload', [RenjaController::class, 'rokeywordReload']);
    Route::get('renja/tagging-reload', [RenjaController::class, 'rotaggingReload']);
    Route::get('renja/sepakati-reload', [RenjaController::class, 'rosepakatiReload']);
    Route::post('renja/listrokeyword', [RenjaController::class, 'listRoKeyword']);

    Route::post('dampak/stunting', [IndikatorStuntingController::class, 'dataStunting']);
    Route::post('dampak/wasting', [IndikatorStuntingController::class, 'dataWasting']);
    Route::post('dampak/underweight', [IndikatorStuntingController::class, 'dataUnderweight']);
    Route::post('dampak/nasional', [IndikatorStuntingController::class, 'dataSwuNasional']);


    Route::post('integrasi/krisnarenja', [IndikatorStuntingController::class, 'renjakrisna']);

    //v2 iqbal
    Route::post('dampak/stuntingsurvey', [IndikatorStuntingController::class, 'dataStuntingSurvey']);
    Route::post('dampak/wastingsurvey', [IndikatorStuntingController::class, 'dataWastingSurvey']);
    Route::post('dampak/underweightsurvey', [IndikatorStuntingController::class, 'dataUnderweightSurvey']);


    Route::post('dampak/stuntingpartial', [IndikatorStuntingController::class, 'dataStuntingPartial']);
    Route::post('dampak/wastingpartial', [IndikatorStuntingController::class, 'dataWastingPartial']);
    Route::post('dampak/underweightpartial', [IndikatorStuntingController::class, 'dataUnderweightPartial']);

    
});

Route::get('lokasi/by-parent/{parentId}', [LocationController::class, 'index']);
Route::get('lokasi/provinsi', [LocationController::class, 'provinsi']);
Route::get('lokasi/kota-kabupaten/{provId?}', [LocationController::class, 'kotaKabupaten']);
Route::get('lokasi/kecamatan/{kotakabId?}', [LocationController::class, 'kecamatan']);
Route::get('lokasi/desa', [LocationController::class, 'desa']);
Route::get('lokasi/prioritas/{tahun}/{tematikId}/{level}', [LocationController::class, 'lokasiPrioritas']);

Route::get('interkoneksi/emonev/komponen/{tahun}', [InterkoneksiEmonevController::class, 'getDataKomponen']);
Route::get('interkoneksi/krisna/dak/{tahun}', [InterkoneksiKrisnaDakController::class, 'getDataDak']);
Route::get('interkoneksi/rkakl/{tahun}/{kddept}', [InterkoneksiRkaklController::class, 'getDataRkakl']);
// Route::get('interkoneksi/krisna/dak-2/{tahun}', [InterkoneksiKrisnaDakController::class, 'getDataDak2']);


Route::get('/data_stunting', 'StuntingDataController@index');

Route::get('/data_stunting/{tahun}/{sumber}', 'StuntingDataController@tahunSumber');

Route::get('/cms/sidebar_menu', 'CmsController@sidebarMenu');