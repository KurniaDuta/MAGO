<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id_mahasiswa' => 1,
                'id_user' => 2, 
                'nim' => '2341720111',
                'id_prodi' => 1,
                'ipk' => 3.5,
                'semester' => 6,
                'created_at' => now(),
            ],
        ];

        DB::table('m_mahasiswa')->insert($data);

    }
}
