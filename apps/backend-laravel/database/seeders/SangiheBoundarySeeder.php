<?php

namespace Database\Seeders;

use App\Services\SangiheBoundaryImportService;
use Illuminate\Database\Seeder;

class SangiheBoundarySeeder extends Seeder
{
    public function run(): void
    {
        app(SangiheBoundaryImportService::class)->import();
    }
}
