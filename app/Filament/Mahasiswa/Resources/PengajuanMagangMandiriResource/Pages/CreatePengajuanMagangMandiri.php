<?php

namespace App\Filament\Mahasiswa\Resources\PengajuanMagangMandiriResource\Pages;

use App\Filament\Mahasiswa\Resources\PengajuanMagangMandiriResource;
use App\Models\Reference\PerusahaanModel;
use App\Models\Reference\LowonganMagangModel;
use App\Models\Reference\PengajuanMagangModel;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class CreatePengajuanMagangMandiri extends CreateRecord
{
    protected static string $resource = PengajuanMagangMandiriResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            'id_mahasiswa' => Auth::user()->mahasiswa->id_mahasiswa,
            'tanggal_pengajuan' => now(),
            'status' => 'Diajukan',
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Get all form data
        $formData = $this->form->getState();

        $hasAcceptedInternship = PengajuanMagangModel::where('id_mahasiswa', Auth::user()->mahasiswa->id_mahasiswa)
            ->where('status', 'Diterima')
            ->whereHas('lowongan', function ($query) use ($formData) {
                $query->where('id_periode', $formData['id_periode']);
            })
            ->exists();

        if ($hasAcceptedInternship) {
            Notification::make()
                ->danger()
                ->title('Pengajuan tidak dapat dilakukan')
                ->body('Anda sudah memiliki pengajuan magang yang diterima pada periode yang sama.')
                ->persistent()
                ->send();

            $this->halt();
        }

        return DB::transaction(function () use ($formData, $data) {

            $idPerusahaan = null;

            if ($formData['perusahaan_tipe'] === 'lama') {
                $idPerusahaan = $formData['id_perusahaan_lama'];
            } else {
                $perusahaan = PerusahaanModel::create([
                    'nama' => $formData['nama_perusahaan'],
                    'alamat' => $formData['alamat_perusahaan'],
                    'no_telepon' => $formData['no_telepon_perusahaan'],
                    'email' => $formData['email_perusahaan'],
                    'partnership' => 'Perusahaan Non-Mitra',
                    'website' => $formData['website_perusahaan'] ? $formData['website_perusahaan'] : null,
                ]);
                $idPerusahaan = $perusahaan->id_perusahaan;
            }

            $lowongan = LowonganMagangModel::create([
                'id_jenis_magang' => 4, // Magang Mandiri ID
                'id_perusahaan' => $idPerusahaan,
                'id_daerah_magang' => $formData['id_daerah_magang'],
                'judul_lowongan' => $formData['judul_lowongan'],
                'deskripsi_lowongan' => $formData['deskripsi_lowongan'],
                'tanggal_posting' => now(),
                'batas_akhir_lamaran' => $formData['tanggal_mulai'],
                'status' => 'Aktif',
                'id_periode' => $formData['id_periode'],
                'id_waktu_magang' => $formData['id_waktu_magang'],
                'id_insentif' => $formData['id_insentif'],
            ]);

            if (isset($formData['bidang_keahlian']) && is_array($formData['bidang_keahlian'])) {
                foreach ($formData['bidang_keahlian'] as $bidangId) {
                    DB::table('r_lowongan_bidang')->insert([
                        'id_lowongan' => $lowongan->id_lowongan,
                        'id_bidang' => $bidangId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            $pengajuan = PengajuanMagangModel::create([
                'id_mahasiswa' => $data['id_mahasiswa'],
                'id_lowongan' => $lowongan->id_lowongan,
                'tanggal_pengajuan' => $data['tanggal_pengajuan'],
                'status' => $data['status'],
            ]);

            return $pengajuan;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Pengajuan magang mandiri berhasil')
            ->body('Pengajuan magang mandiri Anda berhasil dibuat dan akan diproses oleh admin.');
    }
}
