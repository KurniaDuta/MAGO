<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PengajuanMagangSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('t_pengajuan_magang')->delete();
        DB::statement('ALTER TABLE t_pengajuan_magang AUTO_INCREMENT = 1');

        $mahasiswa = DB::table('m_mahasiswa')->get();
        
        // Get periods with their IDs
        $periodeData = DB::table('m_periode')
            ->whereIn('nama_periode', [
                '2024/2025 Ganjil',
                '2024/2025 Genap',
                '2025/2026 Ganjil'
            ])
            ->select('id_periode', 'nama_periode')
            ->get()
            ->keyBy('nama_periode');
            
        // Get lowongan for each periode
        $lowonganByPeriode = [];
        foreach ($periodeData as $nama => $periode) {
            $lowonganByPeriode[$nama] = DB::table('t_lowongan_magang')
                ->where('id_periode', $periode->id_periode)
                ->get();
        }

        // Check if lowongan exists for each periode
        foreach ($lowonganByPeriode as $periode => $lowongan) {
            if ($lowongan->isEmpty()) {
                throw new \Exception("TIDAK ADA LOWONGAN pada periode $periode. Cek LowonganMagangSeeder!");
            }
        }
        
        if ($mahasiswa->isEmpty()) {
            throw new \Exception("TIDAK ADA MAHASISWA!");
        }

        $alasan_penolakan_profesional = [
            'Motivasi yang disampaikan pada surat pengantar belum menunjukkan minat yang kuat terhadap posisi magang ini.',
            'Hasil evaluasi menunjukkan kemampuan komunikasi perlu ditingkatkan untuk mendukung peran yang dituju.',
            'Proposal pengajuan magang yang diajukan kurang menggambarkan pemahaman terhadap bidang kerja di perusahaan.',
            'Belum ada pengalaman organisasi atau kegiatan yang relevan untuk mendukung pelaksanaan tugas magang.',
            'Terdapat kandidat lain yang lebih sesuai dengan kebutuhan dan profil posisi magang saat ini.',
            'Surat pengantar atau dokumen yang diberikan belum secara jelas menjabarkan tujuan dan harapan selama magang.',
            'Pengetahuan dasar terkait bidang magang dinilai masih kurang memadai.',
            'Kesiapan waktu pelaksanaan magang tidak sesuai dengan jadwal yang telah ditetapkan oleh perusahaan.',
            'Rekomendasi dari dosen pembimbing belum mendukung aspek softskill yang diperlukan.',
            'Kandidat diharapkan dapat memperbaiki dan meningkatkan keterampilan interpersonal sebelum mengajukan kembali.',
        ];

        $counter = 1;
        $data = [];
        $existing = [];

        // Periode 1: 2024/2025 Ganjil (April-Juni 2024) - Status: Diterima/Ditolak
        $this->generatePengajuan(
            $data, 
            $existing, 
            $counter, 
            Carbon::create(2024, 4, 1), 
            Carbon::create(2024, 6, 30), 
            $mahasiswa, 
            $lowonganByPeriode['2024/2025 Ganjil'], 
            ['Diterima', 'Ditolak'],
            $alasan_penolakan_profesional
        );

        // Periode 2: 2024/2025 Genap (Oktober-Desember 2024) - Status: Diterima/Ditolak
        $this->generatePengajuan(
            $data, 
            $existing, 
            $counter, 
            Carbon::create(2024, 10, 1), 
            Carbon::create(2024, 12, 30), 
            $mahasiswa, 
            $lowonganByPeriode['2024/2025 Genap'], 
            ['Diterima', 'Ditolak'],
            $alasan_penolakan_profesional
        );

        // Periode 3: 2025/2026 Ganjil (April-Juni 2025) - Status: Diajukan
        $this->generatePengajuan(
            $data, 
            $existing, 
            $counter, 
            Carbon::create(2025, 4, 1), 
            Carbon::create(2025, 6, 15), 
            $mahasiswa, 
            $lowonganByPeriode['2025/2026 Ganjil'], 
            ['Diajukan'],
            $alasan_penolakan_profesional
        );

        DB::table('t_pengajuan_magang')->insert($data);
        $this->command->info('Berhasil menyeeder ' . count($data) . ' data pengajuan magang');
    }

    private function generatePengajuan(&$data, &$existing, &$counter, $startDate, $endDate, $mahasiswa, $lowongan, $allowedStatus, $alasanPenolakan)
    {
        // Generate 4 pengajuan per bulan for the given period
        for ($date = $startDate->copy(); $date <= $endDate; $date->addMonth()) {
            for ($i = 0; $i < 4; $i++) {
                $m = $mahasiswa->random();
                $l = $lowongan->random();
                $key = $m->id_mahasiswa.'-'.$l->id_lowongan;
                if (isset($existing[$key])) continue;
                $existing[$key] = true;

                $tanggal_pengajuan = $date->copy()->addDays(rand(0, min(27, $endDate->copy()->endOfMonth()->day - $date->day)));
                
                // Make sure pengajuan date doesn't exceed period end date
                if ($tanggal_pengajuan > $endDate) {
                    $tanggal_pengajuan = $endDate->copy();
                }
                
                // Select status from allowed statuses for this period
                $status = $allowedStatus[array_rand($allowedStatus)];

                $tanggal_diterima = null;
                $alasan_penolakan = null;
                
                if ($status == 'Diterima') {
                    $tanggal_diterima = $tanggal_pengajuan->copy()->addDays(rand(1, 10));
                    if ($tanggal_diterima > $endDate) {
                        $tanggal_diterima = $endDate->copy();
                    }
                } elseif ($status == 'Ditolak') {
                    $alasan_penolakan = $alasanPenolakan[array_rand($alasanPenolakan)];
                }

                $data[] = [
                    'id_pengajuan' => $counter++,
                    'id_mahasiswa' => $m->id_mahasiswa,
                    'id_lowongan' => $l->id_lowongan,
                    'tanggal_pengajuan' => $tanggal_pengajuan->format('Y-m-d'),
                    'status' => $status,
                    'tanggal_diterima' => $tanggal_diterima ? $tanggal_diterima->format('Y-m-d') : null,
                    'alasan_penolakan' => $alasan_penolakan,
                    'created_at' => $tanggal_pengajuan->format('Y-m-d H:i:s'),
                    'updated_at' => $tanggal_diterima ? $tanggal_diterima->format('Y-m-d H:i:s') : $tanggal_pengajuan->format('Y-m-d H:i:s'),
                ];
            }
        }

        // Add more random entries to ensure we have enough data
        $targetCount = 50; // Aim for 50 entries per period
        $currentCount = count($data);
        $additionalNeeded = max(0, $targetCount - $currentCount);
        
        for ($i = 0; $i < $additionalNeeded; $i++) {
            $m = $mahasiswa->random();
            $l = $lowongan->random();
            $key = $m->id_mahasiswa.'-'.$l->id_lowongan;
            if (isset($existing[$key])) continue;
            $existing[$key] = true;
            
            $tanggal_pengajuan = $startDate->copy()->addDays(rand(0, $endDate->diffInDays($startDate)));
            
            $status = $allowedStatus[array_rand($allowedStatus)];
            
            $tanggal_diterima = null;
            $alasan_penolakan = null;
            
            if ($status == 'Diterima') {
                $tanggal_diterima = $tanggal_pengajuan->copy()->addDays(rand(1, 10));
                if ($tanggal_diterima > $endDate) {
                    $tanggal_diterima = $endDate->copy();
                }
            } elseif ($status == 'Ditolak') {
                $alasan_penolakan = $alasanPenolakan[array_rand($alasanPenolakan)];
            }
            
            $data[] = [
                'id_pengajuan' => $counter++,
                'id_mahasiswa' => $m->id_mahasiswa,
                'id_lowongan' => $l->id_lowongan,
                'tanggal_pengajuan' => $tanggal_pengajuan->format('Y-m-d'),
                'status' => $status,
                'tanggal_diterima' => $tanggal_diterima ? $tanggal_diterima->format('Y-m-d') : null,
                'alasan_penolakan' => $alasan_penolakan,
                'created_at' => $tanggal_pengajuan->format('Y-m-d H:i:s'),
                'updated_at' => $tanggal_diterima ? $tanggal_diterima->format('Y-m-d H:i:s') : $tanggal_pengajuan->format('Y-m-d H:i:s'),
            ];
        }
    }
}