<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Outlet;
use App\Models\Table;
use App\Models\TableArea;

class TableSeeder extends Seeder
{
    public function run()
    {
        // Ambil Outlet pertama
        $outlet = Outlet::first();

        if (!$outlet) {
            $this->command->info('Outlet belum ada. Jalankan php artisan db:seed dulu.');
            return;
        }

        // 1. Cek atau Buat Area
        $areaIndoor = TableArea::firstOrCreate(
            ['outlet_id' => $outlet->id, 'name' => 'Indoor AC'],
            ['is_active' => true]
        );

        // 2. Buat 10 Meja (Cek dulu biar tidak duplikat)
        for ($i = 1; $i <= 10; $i++) {
            Table::firstOrCreate(
                [
                    'outlet_id' => $outlet->id,
                    'table_number' => (string)$i // Kunci unik (Outlet + No Meja)
                ],
                [
                    // Data ini hanya dipakai jika meja belum ada
                    'table_area_id' => $areaIndoor->id,
                    'capacity' => 4,
                    'status' => 'available',
                    'qr_code' => 'TBL-' . ($outlet->code ?? 'OUTLET') . '-' . $i,
                    'is_active' => true
                ]
            );
        }
        
        $this->command->info('Data Meja berhasil dipastikan ada.');
    }
}