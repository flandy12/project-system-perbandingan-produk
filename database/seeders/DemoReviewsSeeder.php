<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoReviewsSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new \RuntimeException('Ulasan simulasi hanya boleh dibuat di lingkungan local/testing.');
        }

        DB::transaction(function () {
            $texts = [
                'Contoh ulasan positif untuk menguji tampilan dan perhitungan rating.',
                'Contoh ulasan netral untuk menguji variasi rating dan urutan produk.',
                'Contoh ulasan kritis untuk menguji distribusi bintang. Bukan pengalaman penggunaan perangkat.',
            ];
            $users = collect(range(1, 3))->map(fn ($i) => User::firstOrCreate(
                ['email' => "demo-review-{$i}@example.invalid"],
                ['name' => "Penguji Simulasi {$i}", 'password' => Hash::make(Str::random(48)), 'is_active' => false]
            ));
            foreach (Product::where('status', 'active')->orderBy('id')->get()->values() as $index => $product) {
                foreach ($users as $i => $user) {
                    $text = '[SIMULASI PENGUJIAN] '.$texts[$i];
                    Rating::updateOrCreate(['product_id' => $product->id, 'user_id' => $user->id], [
                        'rating' => [5, 4, 3, 2][($index + $i) % 4], 'review' => $text,
                    ]);
                    $product->comments()->updateOrCreate(['user_id' => $user->id, 'parent_id' => null], [
                        'comment' => $text, 'status' => 'approved',
                    ]);
                }
            }
        });
    }
}
