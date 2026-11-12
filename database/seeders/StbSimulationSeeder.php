<?php

namespace Database\Seeders;

use App\Models\StbAnalysisRun;
use App\Models\User;
use App\Services\PublishedStbRecommendations;
use App\Services\StbKMeans;
use App\Services\StbSimulationDataset;
use Illuminate\Database\Seeder;

class StbSimulationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::role('admin')->orderBy('id')->first();
        if (! $user) {
            throw new \RuntimeException('Akun admin diperlukan untuk pencatatan simulasi.');
        }
        $period = \Carbon\CarbonImmutable::now()->subMonthNoOverflow();
        foreach (array_keys(config('stb_analysis.types')) as $type) {
            $snapshot = app(StbSimulationDataset::class)->build($period->year, $period->month, $type);
            $snapshot['requested_k'] = null;
            $snapshot['fingerprint'] = PublishedStbRecommendations::fingerprint($snapshot);
            $existing = StbAnalysisRun::where('is_simulated', true)->where('year', $period->year)->where('month', $period->month)
                ->where('device_type', $type)->where('sales_source', $snapshot['sales_source'])->latest()->first();
            if ($existing && ($existing->snapshot['fingerprint'] ?? '') === $snapshot['fingerprint']) {
                continue;
            }
            $result = null;
            $status = 'insufficient';
            try {
                $result = app(StbKMeans::class)->analyze(array_column($snapshot['rows'], 'values'));
                $status = 'ready';
            } catch (\InvalidArgumentException $exception) {
                $snapshot['blocking_reason'] = $exception->getMessage();
            }
            $run = StbAnalysisRun::create(['user_id' => $user->id, 'year' => $period->year, 'month' => $period->month, 'device_type' => $type,
                'sales_source' => $snapshot['sales_source'], 'status' => $status, 'snapshot' => $snapshot, 'result' => $result, 'is_simulated' => true]);
            $this->command?->info('Simulasi #'.$run->id.' '.$type.': '.count($snapshot['rows']).' entri, '.$status);
        }
    }
}
