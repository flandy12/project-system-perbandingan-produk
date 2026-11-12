<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StbSpecificationsRefreshSeeder extends Seeder
{
    public function run(): void
    {
        $read = fn ($name) => json_decode(file_get_contents(base_path('docs/data/'.$name)), true, flags: JSON_THROW_ON_ERROR);
        $result = app(\App\Services\RefreshStbSpecifications::class)->apply(
            $read('stb-specifications-refresh-2026-11-06.json'), $read('stb-catalogue-2026-10-04.json'));
        $this->command?->info(json_encode($result, JSON_UNESCAPED_UNICODE));
    }
}
