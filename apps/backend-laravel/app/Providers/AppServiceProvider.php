<?php

namespace App\Providers;

use App\Models\NilaiDataMentah;
use App\Models\PengajuanData;
use App\Observers\NilaiDataMentahObserver;
use App\Observers\PengajuanDataObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        PengajuanData::observe(PengajuanDataObserver::class);
        NilaiDataMentah::observe(NilaiDataMentahObserver::class);
    }
}
