<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Public\MapController;
use App\Http\Controllers\Api\Public\PublicPortalController;
use App\Http\Controllers\Api\Public\StatisticsController;
use Illuminate\Support\Facades\Route;

$publicRoutes = function (): void {
    Route::get('/summary', [PublicPortalController::class, 'summary']);
    Route::get('/kecamatan', [PublicPortalController::class, 'kecamatan']);
    Route::get('/desa', [PublicPortalController::class, 'desa']);
    Route::get('/portal-settings', [PublicPortalController::class, 'portalSettings']);
    Route::get('/peta/layers', [MapController::class, 'layers']);
    Route::get('/peta/features', [MapController::class, 'allFeatures']);
    Route::get('/peta/workbench', [MapController::class, 'workbench']);
    Route::get('/statistik', [StatisticsController::class, 'index']);
    Route::get('/statistik/wilayah', [StatisticsController::class, 'wilayah']);
    Route::get('/konten', [PublicPortalController::class, 'konten']);
    Route::get('/konten/{slug}', [PublicPortalController::class, 'kontenShow']);
    Route::get('/sumber-data', [PublicPortalController::class, 'sumberData']);
    Route::get('/open-data', [StatisticsController::class, 'openData']);
    Route::get('/open-data.csv', [StatisticsController::class, 'openDataCsv']);
    Route::get('/home', [PublicPortalController::class, 'home']);
    Route::get('/hero', [PublicPortalController::class, 'hero']);
    Route::get('/search', [PublicPortalController::class, 'search']);
    Route::get('/departments', [PublicPortalController::class, 'departments']);
    Route::get('/documents', [PublicPortalController::class, 'documents']);
    Route::get('/pages/{slug}', [PublicPortalController::class, 'pageShow']);
    Route::get('/statistics', [StatisticsController::class, 'index']);
    Route::get('/map/layers', [MapController::class, 'layers']);
    Route::get('/map/workbench', [MapController::class, 'workbench']);
};

Route::prefix('public')->group($publicRoutes);

Route::prefix('v1')->group(function (): void {
    Route::prefix('public')->group(function (): void {
        Route::get('/summary', [PublicPortalController::class, 'summary']);
        Route::get('/kecamatan', [PublicPortalController::class, 'kecamatan']);
        Route::get('/desa', [PublicPortalController::class, 'desa']);
        Route::get('/portal-settings', [PublicPortalController::class, 'portalSettings']);
        Route::get('/peta/layers', [MapController::class, 'layers']);
        Route::get('/peta/features', [MapController::class, 'allFeatures']);
        Route::get('/peta/workbench', [MapController::class, 'workbench']);
        Route::get('/statistik/wilayah', [StatisticsController::class, 'wilayah']);
        Route::get('/konten', [PublicPortalController::class, 'konten']);
        Route::get('/konten/{slug}', [PublicPortalController::class, 'kontenShow']);
        Route::get('/sumber-data', [PublicPortalController::class, 'sumberData']);
        Route::get('/open-data', [StatisticsController::class, 'openData']);
        Route::get('/open-data.csv', [StatisticsController::class, 'openDataCsv']);
        Route::get('/home', [PublicPortalController::class, 'home']);
        Route::get('/hero', [PublicPortalController::class, 'hero']);
        Route::get('/search', [PublicPortalController::class, 'search']);
        Route::get('/departments', [PublicPortalController::class, 'departments']);
        Route::get('/documents', [PublicPortalController::class, 'documents']);
        Route::get('/news', [PublicPortalController::class, 'news']);
        Route::get('/news/{berita:slug}', [PublicPortalController::class, 'newsShow']);
        Route::get('/pages/{slug}', [PublicPortalController::class, 'pageShow']);
        Route::get('/statistics', [StatisticsController::class, 'index']);
        Route::get('/statistics/{indicator:kode}', [StatisticsController::class, 'show']);
        Route::get('/map/layers', [MapController::class, 'layers']);
        Route::get('/map/workbench', [MapController::class, 'workbench']);
        Route::get('/map/layers/{layer:slug}/features', [MapController::class, 'features']);
    });

    Route::prefix('auth')->group(function (): void {
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });
});
