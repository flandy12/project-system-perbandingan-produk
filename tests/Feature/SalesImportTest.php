<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSalesStat;
use App\Models\SalesImportBatch;
use App\Models\User;
use App\Services\SalesImport;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $category = Category::create(['name' => 'STB', 'is_active' => true]);
    $this->product = Product::create(['title' => 'Test STB', 'category_id' => $category->id, 'price' => 100, 'status' => 'active']);
    $this->product->forceFill(['product_code' => 'STB-TEST'])->save();
    $this->input = ['line' => 7, 'values' => ['kode_produk' => 'STB-TEST', 'tahun' => 2025, 'bulan' => 7, 'unit_terjual' => 0, 'sumber' => 'Laporan toko', 'referensi_sumber' => 'LAP-2025-07', 'status_verifikasi' => 'verified', 'is_simulasi' => 0]];
    $this->import = new SalesImport;
});

it('preserves explicit zero and rejects missing units, future periods and unknown products', function () {
    expect($this->import->validateRows([$this->input])[0]['errors'])->toBe([]);
    foreach (['unit_terjual' => null, 'kode_produk' => 'UNKNOWN', 'tahun' => 2099] as $field => $value) {
        $input = $this->input;
        $input['values'][$field] = $value;
        expect($this->import->validateRows([$input])[0]['errors'])->not->toBe([]);
    }
});

it('flags both duplicate rows and formulas', function () {
    $rows = $this->import->validateRows([$this->input, $this->input]);
    expect($rows[0]['errors'])->not->toBe([])->and($rows[1]['errors'])->not->toBe([]);
    $input = $this->input;
    $input['formula'] = true;
    expect($this->import->validateRows([$input])[0]['errors'])->not->toBe([]);
});

it('applies once without overwriting existing sales and rejects repeat applications', function () {
    $user = User::factory()->create();
    $rows = $this->import->validateRows([$this->input]);
    $batch = SalesImportBatch::create(['user_id' => $user->id, 'filename' => 'test.xlsx', 'file_hash' => str_repeat('a', 64), 'rows' => $rows]);
    expect($this->import->apply($batch))->toBe(['inserted' => 1, 'skipped' => 0]);
    $this->assertDatabaseHas('product_sales_stats', ['total_sold' => 0, 'source' => 'Laporan toko', 'verification_status' => 'verified']);
    expect(fn () => $this->import->apply($batch))->toThrow(ValidationException::class);
    $input = $this->input;
    $input['values']['unit_terjual'] = 999;
    $second = SalesImportBatch::create(['user_id' => $user->id, 'filename' => 'second.xlsx', 'file_hash' => str_repeat('b', 64), 'rows' => $this->import->validateRows([$input])]);
    expect($this->import->apply($second))->toBe(['inserted' => 0, 'skipped' => 1]);
    expect(ProductSalesStat::first()->total_sold)->toBe(0);
});

it('does not partially save a batch containing an invalid row', function () {
    $bad = $this->input;
    $bad['values']['bulan'] = 8;
    $bad['values']['unit_terjual'] = null;
    $batch = SalesImportBatch::create(['user_id' => User::factory()->create()->id, 'filename' => 'test.xlsx', 'file_hash' => str_repeat('c', 64), 'rows' => $this->import->validateRows([$this->input, $bad])]);
    expect(fn () => $this->import->apply($batch))->toThrow(ValidationException::class);
    expect(ProductSalesStat::count())->toBe(0)->and($batch->fresh()->status)->toBe('preview');
});

it('reads the documented Excel layout and rejects a formula cell', function () {
    $book = new Spreadsheet;
    $sheet = $book->getActiveSheet()->setTitle('Penjualan');
    $sheet->fromArray(SalesImport::HEADERS, null, 'A6');
    $sheet->fromArray(array_values($this->input['values']), null, 'A7', true);
    $path = tempnam(sys_get_temp_dir(), 'sales-test-');
    try {
        (new Xlsx($book))->save($path);
        expect($this->import->read($path)[0]['errors'])->toBe([]);
        $sheet->setCellValue('D7', '=1+1');
        (new Xlsx($book))->save($path);
        expect($this->import->read($path)[0]['errors'])->not->toBe([]);
    } finally {
        unlink($path);
        $book->disconnectWorksheets();
    }
});

it('blocks customers and restricts batch access to the uploader', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('sales-import.index'))->assertForbidden();
    $this->actingAs($user)->post(route('product-sales-stats.store'), [])->assertForbidden();
    Permission::create(['name' => 'view.product.sales', 'guard_name' => 'web']);
    $user->givePermissionTo('view.product.sales');
    $other = User::factory()->create();
    $batch = SalesImportBatch::create(['user_id' => $other->id, 'filename' => 'test.xlsx', 'file_hash' => str_repeat('d', 64), 'rows' => []]);
    $this->actingAs($user)->get(route('sales-import.show', $batch))->assertForbidden();
    $this->actingAs($user)->get(route('sales-import.index'))->assertOk()->assertSee('Kesiapan data');
});

it('enforces the approved actor boundaries and preserves customer review access', function () {
    $this->seed(\Database\Seeders\SalesFoundationSeeder::class);
    $customer = User::factory()->create();
    $customer->assignRole('pelanggan');
    $this->actingAs($customer)->get(route('users.index'))->assertForbidden();
    $this->actingAs($customer)->get(route('products.index'))->assertForbidden();
    $this->actingAs($customer)->post(route('products.comments.store', $this->product), [])->assertSessionHasErrors('comment');
    $manager = User::factory()->create();
    $manager->assignRole('pengelola-data');
    $this->actingAs($manager)->get(route('sales-import.index'))->assertOk();
    $this->actingAs($manager)->get(route('users.index'))->assertForbidden();
    $this->actingAs($manager)->get(route('score-weights.index'))->assertForbidden();
});

it('validates the delivered template layout without treating blank sales as zero', function () {
    $rows = $this->import->read(base_path('outputs/stb-sales-20261006/Template-Penjualan-STB.xlsx'));
    expect($rows)->toHaveCount(19);
    foreach ($rows as $row) {
        expect($row['data']['unit_terjual'])->toBeNull()->and($row['errors'])->not->toBe([]);
    }
});

it('renders existing unverified sales without claiming verification', function () {
    $user = User::factory()->create();
    Permission::create(['name' => 'view.product.sales', 'guard_name' => 'web']);
    $user->givePermissionTo('view.product.sales');
    ProductSalesStat::create(['product_id' => $this->product->id, 'year' => 2025, 'month' => 7, 'total_sold' => 10]);
    $this->actingAs($user)->get(route('sales-import.index', ['year' => 2025, 'month' => 7]))->assertOk()->assertSee('Belum diverifikasi')->assertDontSee('@else')->assertDontSee('Terverifikasi:');
});

it('excludes unverified, simulated and unsourced rows from real analysis', function () {
    foreach ([['verified', false, 'Laporan'], ['unverified', false, 'Laporan'], ['verified', true, 'Laporan'], ['verified', false, null]] as $i => [$status, $simulation, $source]) {
        ProductSalesStat::create(['product_id' => $this->product->id, 'year' => 2025, 'month' => $i + 1, 'total_sold' => 0, 'verification_status' => $status, 'is_simulated' => $simulation, 'source' => $source, 'source_reference' => 'LAP-1']);
    }
    expect(ProductSalesStat::verifiedReal()->pluck('month')->all())->toBe([1]);
});
