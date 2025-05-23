<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Calling all the seeder ~khip

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            ProdiSeeder::class,
            BidangKeahlianSeeder::class,
            JenisMagangSeeder::class,
            LokasiMagangSeeder::class,
            DosenPembimbingSeeder::class,
            AdminSeeder::class,
            MahasiswaSeeder::class,
            PerusahaanSeeder::class,
            PeriodeSeeder::class,
            WaktuMagangSeeder::class,
            InsentifSeeder::class,
        ]);

        // Create spatie roles
        // foreach (['mahasiswa', 'dosen_pembimbing', 'admin'] as $roleName) {
        //     Role::firstOrCreate(['name' => $roleName]);
        // }
    }
}
