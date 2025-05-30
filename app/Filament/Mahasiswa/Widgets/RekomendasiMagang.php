<?php

namespace App\Filament\Mahasiswa\Widgets;

use App\Models\Auth\MahasiswaModel;
use App\Models\Reference\LowonganMagangModel;
use App\Models\Reference\PreferensiMahasiswaModel;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RekomendasiMagang extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Rekomendasi Magang Paling Sesuai';
    protected static ?int $sort = 3;

    protected array $bobot = [
        1 => 0.397,
        2 => 0.297,
        3 => 0.178,
        4 => 0.089,
        5 => 0.038
    ];

    public function table(Table $table): Table
    {
        $rekomendasiCollection = $this->getRekomendasi();

        return $table
            ->query(function () use ($rekomendasiCollection) {
                if (empty($rekomendasiCollection)) {
                    return LowonganMagangModel::whereRaw('1 = 0');
                }

                $orderedIds = $rekomendasiCollection->pluck('id_lowongan')->toArray();
                if (empty($orderedIds)) {
                    return LowonganMagangModel::whereRaw('1 = 0');
                }

                return LowonganMagangModel::query()
                    ->with(['jenisMagang', 'daerahMagang', 'waktuMagang', 'insentif', 'perusahaan'])
                    ->whereIn('id_lowongan', $orderedIds)
                    ->orderByRaw("FIELD(id_lowongan, " . implode(',', $orderedIds) . ")");
            })
            ->columns([
                Tables\Columns\TextColumn::make('judul_lowongan')
                    ->searchable()
                    ->sortable()
                    ->label('Judul Lowongan'),
                Tables\Columns\TextColumn::make('perusahaan.nama')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Perusahaan'),
                Tables\Columns\TextColumn::make('jenisMagang.nama_jenis_magang')
                    ->searchable()
                    ->sortable()
                    ->label('Jenis Magang'),
                Tables\Columns\TextColumn::make('daerahMagang.namaLengkapDenganProvinsi')
                    ->searchable()
                    ->sortable()
                    ->label('Lokasi'),
                Tables\Columns\TextColumn::make('waktuMagang.waktu_magang')
                    ->searchable()
                    ->sortable()
                    ->label('Waktu'),
                Tables\Columns\TextColumn::make('insentif.keterangan')
                    ->searchable()
                    ->sortable()
                    ->label('Insentif'),
            ])
            ->emptyStateHeading('Belum ada lowongan yang tersedia')
            ->emptyStateDescription('Silakan lengkapi preferensi magang Anda untuk mendapatkan rekomendasi lowongan yang sesuai.')
            ->actions([
                // Tables\Actions\Action::make('detail')
                //     ->url(fn($record) => route('filament.mahasiswa.resources.lowongan-magang.detail', $record->id_lowongan))
                //     ->button()
                //     ->label('Lihat Detail'),
            ]);
    }

    protected function getRekomendasi(): Collection
    {
        $mahasiswa = MahasiswaModel::where('id_user', Auth::id())->first();
        if (!$mahasiswa) {
            return collect([]);
        }

        $preferensi = PreferensiMahasiswaModel::where('id_mahasiswa', $mahasiswa->id_mahasiswa)->first();
        if (!$preferensi) {
            return collect([]);
        }

        // Mendapatkan lowongan yang aktif
        $lowonganCollection = LowonganMagangModel::query()
            ->with(['bidangKeahlian', 'jenisMagang', 'daerahMagang', 'waktuMagang', 'insentif', 'perusahaan'])
            ->where('status', 'Selesai')
            ->get();

        if ($lowonganCollection->isEmpty()) {
            return collect([]);
        }

        // Mendapatkan preferensi jenis magang dan bidang keahlian
        $preferensiJenisMagang = $preferensi->jenisMagang->pluck('id_jenis_magang')->toArray();
        $preferensiBidang = $preferensi->bidangKeahlian->pluck('id_bidang')->toArray();

        // Membuat matriks keputusan (matrix)
        $matrix = [];
        $lowonganIds = [];

        // Menambahkan alternatif optimal sebagai baris pertama (A0)
        $matrix[0] = [
            'daerah' => 1,
            'waktu' => 1,
            'insentif' => 1,
            'jenis' => 1,
            'bidang' => 1,
        ];

        foreach ($lowonganCollection as $index => $lowongan) {
            $rowIndex = $index + 1;
            $lowonganIds[$rowIndex] = $lowongan->id_lowongan;

            // Menghitung kesesuaian daerah
            $daerah = ($lowongan->id_daerah_magang == $preferensi->id_daerah_magang) ? 1 : 0;

            // Menghitung kesesuaian waktu
            $waktu = ($lowongan->id_waktu_magang == $preferensi->id_waktu_magang) ? 1 : 0;

            // Menghitung kesesuaian insentif
            $insentif = ($lowongan->id_insentif == $preferensi->id_insentif) ? 1 : 0;

            // Menghitung kesesuaian jenis magang
            $jenis = in_array($lowongan->id_jenis_magang, $preferensiJenisMagang) ? 1 : 0;

            // Menghitung kesesuaian bidang keahlian
            $lowonganBidang = $lowongan->bidangKeahlian->pluck('id_bidang')->toArray();
            $bidangCount = count($lowonganBidang);
            $matchCount = 0;

            if ($bidangCount > 0) {
                foreach ($lowonganBidang as $bidang) {
                    if (in_array($bidang, $preferensiBidang)) {
                        $matchCount++;
                    }
                }
                $bidang = $matchCount / $bidangCount;
            } else {
                $bidang = 0;
            }

            $matrix[$rowIndex] = [
                'daerah' => $daerah,
                'waktu' => $waktu,
                'insentif' => $insentif,
                'jenis' => $jenis,
                'bidang' => $bidang,
            ];
        }

        // Menghitung jumlah setiap kolom untuk normalisasi
        $colSums = [
            'daerah' => 0,
            'waktu' => 0,
            'insentif' => 0,
            'jenis' => 0,
            'bidang' => 0,
        ];

        foreach ($matrix as $row) {
            $colSums['daerah'] += $row['daerah'];
            $colSums['waktu'] += $row['waktu'];
            $colSums['insentif'] += $row['insentif'];
            $colSums['jenis'] += $row['jenis'];
            $colSums['bidang'] += $row['bidang'];
        }

        // Normalisasi matriks
        $normalizedMatrix = [];
        foreach ($matrix as $i => $row) {
            $normalizedMatrix[$i] = [
                'daerah' => ($colSums['daerah'] > 0) ? $row['daerah'] / $colSums['daerah'] : 0,
                'waktu' => ($colSums['waktu'] > 0) ? $row['waktu'] / $colSums['waktu'] : 0,
                'insentif' => ($colSums['insentif'] > 0) ? $row['insentif'] / $colSums['insentif'] : 0,
                'jenis' => ($colSums['jenis'] > 0) ? $row['jenis'] / $colSums['jenis'] : 0,
                'bidang' => ($colSums['bidang'] > 0) ? $row['bidang'] / $colSums['bidang'] : 0,
            ];
        }

        // Normalisasi dengan bobot
        $weightedMatrix = [];
        $weightedMatrix[0] = [
            'daerah' => $normalizedMatrix[0]['daerah'] * $this->bobot[$preferensi->ranking_daerah],
            'waktu' => $normalizedMatrix[0]['waktu'] * $this->bobot[$preferensi->ranking_waktu_magang],
            'insentif' => $normalizedMatrix[0]['insentif'] * $this->bobot[$preferensi->ranking_insentif],
            'jenis' => $normalizedMatrix[0]['jenis'] * $this->bobot[$preferensi->ranking_jenis_magang],
            'bidang' => $normalizedMatrix[0]['bidang'] * $this->bobot[$preferensi->ranking_bidang],
        ];

        // Hitung nilai S dan K untuk setiap alternatif
        $optimalValue = array_sum($weightedMatrix[0]);
        $sValues = [];
        $kValues = [];

        for ($i = 1; $i < count($normalizedMatrix); $i++) {
            $weightedMatrix[$i] = [
                'daerah' => $normalizedMatrix[$i]['daerah'] * $this->bobot[$preferensi->ranking_daerah],
                'waktu' => $normalizedMatrix[$i]['waktu'] * $this->bobot[$preferensi->ranking_waktu_magang],
                'insentif' => $normalizedMatrix[$i]['insentif'] * $this->bobot[$preferensi->ranking_insentif],
                'jenis' => $normalizedMatrix[$i]['jenis'] * $this->bobot[$preferensi->ranking_jenis_magang],
                'bidang' => $normalizedMatrix[$i]['bidang'] * $this->bobot[$preferensi->ranking_bidang],
            ];

            $sValues[$i] = array_sum($weightedMatrix[$i]);
            $kValues[$i] = ($optimalValue > 0) ? $sValues[$i] / $optimalValue : 0;
        }

        // Persiapkan hasil rekomendasi
        $rekomendasiItems = [];
        foreach ($kValues as $i => $kValue) {
            $lowonganId = $lowonganIds[$i];
            $lowongan = $lowonganCollection->firstWhere('id_lowongan', $lowonganId);
            if ($lowongan) {
                $rekomendasiItems[] = [
                    'id_lowongan' => $lowonganId,
                    'kesesuaian' => $kValue,
                    'lowongan' => $lowongan,
                ];
            }
        }

        // Urutkan hasil rekomendasi berdasarkan nilai kesesuaian (tertinggi ke terendah)
        usort($rekomendasiItems, function ($a, $b) {
            return $b['kesesuaian'] <=> $a['kesesuaian'];
        });

        // Kembalikan hasil sebagai collection yang sudah diurutkan
        $lowonganCollectionSorted = collect(array_map(function ($item) {
            return $item['lowongan'];
        }, $rekomendasiItems));

        return $lowonganCollectionSorted;
    }
}
