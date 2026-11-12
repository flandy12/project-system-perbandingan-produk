<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductSalesStat;
use App\Models\SalesImportBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use ZipArchive;

class SalesImport
{
    public const HEADERS = ['kode_produk', 'tahun', 'bulan', 'unit_terjual', 'sumber', 'referensi_sumber', 'status_verifikasi', 'is_simulasi'];

    public function read(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages(['file' => 'Berkas harus berupa Excel .xlsx yang sah.']);
        }
        $size = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $size += $zip->statIndex($i)['size'];
        }
        $count = $zip->numFiles;
        $zip->close();
        if ($size > 30 * 1024 * 1024 || $count > 200) {
            throw ValidationException::withMessages(['file' => 'Isi berkas terlalu besar. Maksimal 30 MB setelah dibuka.']);
        }
        $reader = new Xlsx;
        $reader->setLoadSheetsOnly(['Penjualan']);
        $reader->setReadDataOnly(false);
        try {
            $book = $reader->load($path);
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages(['file' => 'Excel tidak dapat dibaca. Gunakan template yang disediakan.']);
        }
        try {
            $sheet = $book->getSheetByName('Penjualan');
            if (! $sheet || $sheet->getHighestDataRow() > 5006) {
                throw ValidationException::withMessages(['file' => 'Lembar Penjualan wajib tersedia dan maksimal berisi 5.000 baris.']);
            }
            $headers = $sheet->rangeToArray('A6:H6', null, false, false)[0];
            if ($headers !== self::HEADERS) {
                throw ValidationException::withMessages(['file' => 'Judul kolom pada baris 6 berbeda dari template.']);
            }
            $input = [];
            for ($row = 7; $row <= $sheet->getHighestDataRow(); $row++) {
                $values = $sheet->rangeToArray("A{$row}:H{$row}", null, false, false)[0];
                if (collect($values)->every(fn ($v) => $v === null || $v === '')) {
                    continue;
                }
                $hasFormula = false;
                foreach (range('A', 'H') as $column) {
                    $hasFormula = $hasFormula || $sheet->getCell($column.$row)->getDataType() === DataType::TYPE_FORMULA;
                }
                $input[] = ['line' => $row, 'values' => array_combine(self::HEADERS, $values), 'formula' => $hasFormula];
            }
            if (! $input) {
                throw ValidationException::withMessages(['file' => 'Belum ada baris penjualan. Isi periode, unit, dan sumber terlebih dahulu.']);
            }

            return $this->validateRows($input);
        } finally {
            $book->disconnectWorksheets();
        }
    }

    public function validateRows(array $input): array
    {
        $products = Product::whereNotNull('product_code')->get()->keyBy('product_code');
        $seen = [];
        $rows = [];
        foreach ($input as $item) {
            $data = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $item['values']);
            $validator = Validator::make($data, [
                'kode_produk' => ['required', 'string'],
                'tahun' => ['required', 'integer', 'between:2000,'.now()->year],
                'bulan' => ['required', 'integer', 'between:1,12'],
                'unit_terjual' => ['required', 'integer', 'between:0,2147483647'],
                'sumber' => ['required', 'string', 'max:255'],
                'referensi_sumber' => ['required', 'string', 'max:2000'],
                'status_verifikasi' => ['required', 'in:verified,unverified'],
                'is_simulasi' => ['required', 'in:0,1'],
            ]);
            $errors = $validator->errors()->all();
            if ($item['formula'] ?? false) {
                $errors[] = 'Formula tidak diperbolehkan pada data impor.';
            }
            $product = $products->get($data['kode_produk'] ?? null);
            if (! $product) {
                $errors[] = 'Kode produk tidak ditemukan.';
            }
            if ($validator->passes() && sprintf('%04d-%02d', $data['tahun'], $data['bulan']) > now()->format('Y-m')) {
                $errors[] = 'Periode penjualan tidak boleh berada di masa depan.';
            }
            $key = ($data['kode_produk'] ?? '').':'.($data['tahun'] ?? '').':'.($data['bulan'] ?? '');
            if (isset($seen[$key])) {
                $errors[] = 'Produk dan periode berulang dalam berkas yang sama.';
                $rows[$seen[$key]]['errors'][] = 'Produk dan periode berulang dalam berkas yang sama.';
            } else {
                $seen[$key] = count($rows);
            }
            $conflict = $product && $validator->passes() && ProductSalesStat::where('product_id', $product->id)->where('year', $data['tahun'])->where('month', $data['bulan'])->exists();
            $rows[] = ['line' => $item['line'], 'data' => $data, 'product_id' => $product?->id, 'title' => $product?->title, 'errors' => $errors, 'conflict' => (bool) $conflict];
        }

        return $rows;
    }

    public function apply(SalesImportBatch $batch): array
    {
        return DB::transaction(function () use ($batch) {
            $locked = SalesImportBatch::whereKey($batch->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'preview') {
                throw ValidationException::withMessages(['batch' => 'Batch ini sudah diterapkan.']);
            }
            // Revalidate against current products, periods and existing records.
            $rows = $this->validateRows(array_map(fn ($row) => ['line' => $row['line'], 'values' => $row['data']], $locked->rows));
            if (collect($locked->rows)->contains(fn ($row) => count($row['errors']) > 0) || collect($rows)->contains(fn ($row) => count($row['errors']) > 0)) {
                throw ValidationException::withMessages(['batch' => 'Perbaiki semua baris bermasalah sebelum menerapkan impor.']);
            }
            $inserted = 0;
            $skipped = 0;
            foreach ($rows as $row) {
                $data = $row['data'];
                $stat = ProductSalesStat::firstOrCreate([
                    'product_id' => $row['product_id'], 'year' => $data['tahun'], 'month' => $data['bulan'],
                ], [
                    'total_sold' => $data['unit_terjual'], 'source' => $data['sumber'],
                    'source_reference' => $data['referensi_sumber'], 'verification_status' => $data['status_verifikasi'],
                    'is_simulated' => (bool) $data['is_simulasi'], 'sales_import_batch_id' => $locked->id,
                ]);
                $stat->wasRecentlyCreated ? $inserted++ : $skipped++;
            }
            $summary = compact('inserted', 'skipped');
            $locked->update(['status' => 'applied', 'rows' => $rows, 'summary' => $summary, 'applied_at' => now()]);

            return $summary;
        });
    }
}
