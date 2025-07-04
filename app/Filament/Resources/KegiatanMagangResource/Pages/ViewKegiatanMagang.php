<?php

namespace App\Filament\Resources\KegiatanMagangResource\Pages;

use App\Filament\Resources\KegiatanMagangResource;
use App\Models\Reference\PenempatanMagangModel;
use App\Models\Reference\PengajuanMagangModel;
use App\Models\UserModel;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ViewKegiatanMagang extends ViewRecord
{
    protected static string $resource = KegiatanMagangResource::class;
    protected array $formData = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->url(KegiatanMagangResource::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
            Actions\DeleteAction::make()
                ->modalHeading('Hapus Pengajuan Magang')
                ->modalDescription('Apakah Anda yakin ingin menghapus pengajuan magang ini?')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->modalCancelActionLabel('Batal')
                ->hidden(function (PengajuanMagangModel $record) {
                    return $record->status === 'Diterima';
                }),
            Actions\Action::make('editStatus')
                ->label('Edit Status')
                ->icon('heroicon-o-pencil')
                ->form([
                    Forms\Components\Section::make('Status Pengajuan')
                        ->schema([
                            Forms\Components\Select::make('status')
                                ->label('Status Pengajuan')
                                ->options([
                                    'Diajukan' => 'Diajukan',
                                    'Diterima' => 'Diterima',
                                    'Ditolak' => 'Ditolak',
                                ])
                                ->afterStateHydrated(function ($component, $state) {
                                    if (!$state) {
                                        $record = $this->getRecord();
                                        $component->state($record->status);
                                    }
                                })
                                ->live()
                                ->afterStateUpdated(function (string $state, callable $set) {
                                    if ($state === 'Diterima') {
                                        $set('tanggal_diterima', now()->format('Y-m-d'));
                                    }
                                })
                                ->required(),

                            Forms\Components\DatePicker::make('tanggal_diterima')
                                ->label('Tanggal Diterima')
                                ->displayFormat('Y-m-d')
                                ->visible(fn(Forms\Get $get) => $get('status') === 'Diterima')
                                ->disabled()
                                ->afterStateHydrated(function ($component, $state) {
                                    if (!$state) {
                                        $record = $this->getRecord();
                                        $component->state($record->tanggal_diterima ? $record->tanggal_diterima->format('Y-m-d') : now()->format('Y-m-d'));
                                    }
                                })
                        ])->columns(2),

                    Forms\Components\Section::make('Dosen Pembimbing')
                        ->schema([
                            Forms\Components\Select::make('dosen_pembimbing')
                                ->label('Dosen Pembimbing')
                                ->options(function () {
                                    $dosenOptions = [];
                                    $dosenList = \App\Models\Auth\DosenPembimbingModel::query()
                                        ->with('bidangKeahlian', 'user')
                                        ->get();

                                    foreach ($dosenList as $dosen) {
                                        $bidangKeahlian = $dosen->bidangKeahlian->pluck('nama_bidang_keahlian')->toArray();
                                        $bidangText = !empty($bidangKeahlian)
                                            ? implode(', ', $bidangKeahlian)
                                            : 'Tidak Ada';

                                        $dosenOptions[$dosen->id_dospem] = $dosen->user->nama . ' (' . $bidangText . ')';
                                    }

                                    return $dosenOptions;
                                })
                                ->searchable()
                                ->required(fn(Forms\Get $get) => $get('status') === 'Diterima')
                                ->visible(fn(Forms\Get $get) => $get('status') === 'Diterima'),
                        ])
                        ->visible(fn(Forms\Get $get) => $get('status') === 'Diterima'),

                    Forms\Components\Section::make('Catatan Penolakan')
                        ->schema([
                            Forms\Components\RichEditor::make('alasan_penolakan')
                                ->required(fn(Forms\Get $get) => $get('status') === 'Ditolak')
                                ->visible(fn(Forms\Get $get) => $get('status') === 'Ditolak'),
                        ])
                        ->visible(fn(Forms\Get $get) => $get('status') === 'Ditolak'),
                ])
                ->action(function (array $data) {
                    $this->formData = $data;
                    $this->saveStatus();
                })
                ->modalHeading('Edit Status Pengajuan Magang')
                ->modalSubmitActionLabel('Simpan')
                ->modalWidth('lg')
                ->mountUsing(function (Forms\Form $form) {
                    $record = $this->getRecord();

                    $form->fill([
                        'status' => $record->status,
                        'tanggal_diterima' => $record->tanggal_diterima,
                        'alasan_penolakan' => $record->alasan_penolakan,
                    ]);

                    if ($record->status === 'Diterima') {
                        $tanggalDiterima = DB::table('t_pengajuan_magang')
                            ->where('id_pengajuan', $record->id_pengajuan)
                            ->value('tanggal_diterima');

                        if ($tanggalDiterima) {
                            $form->fill([
                                'tanggal_diterima' => $tanggalDiterima
                            ]);
                        }
                    }

                    if ($record->penempatan) {
                        $dosenPembimbing = DB::table('r_bimbingan')
                            ->where('id_penempatan', $record->penempatan->id_penempatan)
                            ->first();

                        if ($dosenPembimbing) {
                            $form->fill([
                                'dosen_pembimbing' => $dosenPembimbing->id_dospem
                            ]);
                        }
                    }
                }),
        ];
    }

    protected function saveStatus()
    {
        $record = $this->getRecord();

        $record->status = $this->formData['status'];

        if ($this->formData['status'] === 'Diterima') {
            $record->tanggal_diterima = $this->formData['tanggal_diterima'] ?? now();

            $this->rejectOtherApplications($record);
        } elseif ($this->formData['status'] === 'Ditolak') {
            $record->alasan_penolakan = $this->formData['alasan_penolakan'] ?? null;
        }

        $record->save();

        $mahasiswa = UserModel::whereHas('mahasiswa', function ($query) use ($record) {
            $query->where('id_mahasiswa', $record->id_mahasiswa);
        })->first();

        if ($record->status === 'Diterima') {
            $dosenPembimbing = $this->formData['dosen_pembimbing'] ?? null;
            $dosenPembimbingNama = null;
            $dosenUser = null;

            if ($dosenPembimbing) {
                $dosenUser = UserModel::whereHas('dosenPembimbing', function ($query) use ($dosenPembimbing) {
                    $query->where('id_dospem', $dosenPembimbing);
                })->first();

                if ($dosenUser) {
                    $dosenPembimbingNama = $dosenUser->nama;
                }
            }

            if ($dosenUser) {
                Notification::make()
                    ->title('Pengajuan Magang Diterima')
                    ->body('Anda telah ditunjuk sebagai dosen pembimbing untuk mahasiswa ' . ($mahasiswa->nama ?? '-') . ' pada pengajuan magang berjudul "' . ($record->lowongan->judul_lowongan ?? '-') . '".')
                    ->success()
                    ->persistent()
                    ->icon('heroicon-o-check-circle')
                    ->sendToDatabase($dosenUser);
            }

            if ($mahasiswa) {
                Notification::make()
                    ->title('Pengajuan Magang Diterima')
                    ->body('Selamat! Pengajuan magang Anda telah diterima di ' . ($record->lowongan->judul_lowongan ?? '-') .
                        ($dosenPembimbingNama ? '. Dosen pembimbing Anda adalah ' . $dosenPembimbingNama : ''))
                    ->success()
                    ->persistent()
                    ->icon('heroicon-o-check-circle')
                    ->sendToDatabase($mahasiswa);
            }

            $penempatan = PenempatanMagangModel::where('id_pengajuan', $record->id_pengajuan)->first();

            DB::beginTransaction();
            try {
                if (!$penempatan) {
                    $penempatan = PenempatanMagangModel::create([
                        'id_mahasiswa' => $record->id_mahasiswa,
                        'id_pengajuan' => $record->id_pengajuan,
                        'status' => PenempatanMagangModel::STATUS_BERLANGSUNG,
                    ]);

                    if ($dosenPembimbing) {
                        DB::table('r_bimbingan')->insert([
                            'id_dospem' => $dosenPembimbing,
                            'id_penempatan' => $penempatan->id_penempatan,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    DB::commit();

                    $admin = auth()->user();
                    Notification::make()
                        ->title('Penempatan magang berhasil dibuat')
                        ->success()
                        ->persistent()
                        ->icon('heroicon-o-check-circle')
                        ->sendToDatabase($admin);
                } else {
                    if ($dosenPembimbing) {
                        $existingBimbingan = DB::table('r_bimbingan')
                            ->where('id_penempatan', $penempatan->id_penempatan)
                            ->first();

                        if ($existingBimbingan) {
                            if ($existingBimbingan->id_dospem != $dosenPembimbing) {
                                DB::table('r_bimbingan')
                                    ->where('id_penempatan', $penempatan->id_penempatan)
                                    ->update([
                                        'id_dospem' => $dosenPembimbing,
                                        'updated_at' => now(),
                                    ]);
                            }
                        } else {
                            DB::table('r_bimbingan')->insert([
                                'id_dospem' => $dosenPembimbing,
                                'id_penempatan' => $penempatan->id_penempatan,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }

                    DB::commit();
                }
            } catch (\Exception $e) {
                DB::rollBack();

                Notification::make()
                    ->title('Error membuat penempatan magang')
                    ->body($e->getMessage())
                    ->danger()
                    ->persistent()
                    ->send();
                return;
            }
        } elseif ($record->status === 'Ditolak') {
            if ($mahasiswa) {
                Notification::make()
                    ->title('Pengajuan Magang Ditolak')
                    ->body('Maaf, pengajuan magang ' . ($record->lowongan->judul_lowongan ?? '-') . ' Anda telah ditolak.' .
                        ($record->alasan_penolakan ? ' Alasan penolakan: ' . strip_tags($record->alasan_penolakan) : ''))
                    ->danger()
                    ->persistent()
                    ->icon('heroicon-o-x-circle')
                    ->sendToDatabase($mahasiswa);
            }
        }

        Notification::make()
            ->title('Status pengajuan berhasil diperbarui')
            ->success()
            ->send();

        $this->refreshFormData();
    }

    protected function rejectOtherApplications(PengajuanMagangModel $acceptedApplication)
    {
        $periode = null;
        if ($acceptedApplication->lowongan && $acceptedApplication->lowongan->periode) {
            $periode = $acceptedApplication->lowongan->periode->id_periode;
        }

        if (!$periode) {
            return;
        }

        $otherApplications = PengajuanMagangModel::where('id_mahasiswa', $acceptedApplication->id_mahasiswa)
            ->where('id_pengajuan', '!=', $acceptedApplication->id_pengajuan)
            ->where('status', '!=', 'Ditolak')
            ->whereHas('lowongan', function ($query) use ($periode) {
                $query->where('id_periode', $periode);
            })
            ->get();

        if ($otherApplications->isEmpty()) {
            return;
        }

        $mahasiswa = UserModel::whereHas('mahasiswa', function ($query) use ($acceptedApplication) {
            $query->where('id_mahasiswa', $acceptedApplication->id_mahasiswa);
        })->first();

        foreach ($otherApplications as $application) {
            // Update status to rejected
            $application->status = 'Ditolak';
            $application->alasan_penolakan = 'Ditolak otomatis karena sudah menerima tawaran magang lain pada periode yang sama.';
            $application->save();

            // Send notification to student
            if ($mahasiswa) {
                Notification::make()
                    ->title('Pengajuan Magang Otomatis Ditolak')
                    ->body('Pengajuan magang Anda untuk "' . ($application->lowongan->judul_lowongan ?? '-') .
                        '" ditolak secara otomatis karena Anda telah diterima di "' .
                        ($acceptedApplication->lowongan->judul_lowongan ?? '-') . '" pada periode yang sama.')
                    ->warning()
                    ->persistent()
                    ->icon('heroicon-o-information-circle')
                    ->sendToDatabase($mahasiswa);
            }
        }

        $admin = auth()->user();
        if ($admin) {
            Notification::make()
                ->title($otherApplications->count() . ' pengajuan lain otomatis ditolak')
                ->body('Pengajuan lain dari mahasiswa ' . ($mahasiswa->nama ?? '-') .
                    ' pada periode yang sama telah otomatis ditolak karena sudah diterima di tempat lain.')
                ->info()
                ->persistent()
                ->sendToDatabase($admin);
        }
    }

    public function refreshFormData(array $attributes = []): void
    {
        $record = $this->getRecord();
        $key = $record->getKey();

        $this->record = $this->resolveRecord($key);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Mahasiswa')
                    ->schema([
                        TextEntry::make('mahasiswa.user.nama')
                            ->label('Nama Mahasiswa'),

                        TextEntry::make('mahasiswa.nim')
                            ->label('NIM'),

                        TextEntry::make('mahasiswa.prodi.nama_prodi')
                            ->label('Program Studi'),

                        TextEntry::make('mahasiswa.ipk')
                            ->label('IPK Mahasiswa'),

                        TextEntry::make('mahasiswa.semester')
                            ->label('Semester Mahasiswa'),

                        TextEntry::make('tanggal_pengajuan')
                            ->label('Tanggal Pengajuan')
                            ->date('Y-m-d'),

                        TextEntry::make('status')
                            ->badge()
                            ->label('Status Pengajuan')
                            ->color(fn(string $state): string => match ($state) {
                                'Diajukan' => 'warning',
                                'Diterima' => 'success',
                                'Ditolak' => 'danger',
                                default => 'gray',
                            }),

                        Section::make('Preferensi Mahasiswa')
                            ->schema([
                                TextEntry::make('mahasiswa.preferensi.daerahMagang.namaLengkapDenganProvinsi')
                                    ->label('Daerah Magang')
                                    ->default('Semua')
                                    ->placeholder('Semua'),

                                TextEntry::make('jenisMagang')
                                    ->label('Jenis Magang')
                                    ->default('Semua')
                                    ->placeholder('Semua')
                                    ->getStateUsing(function ($record) {
                                        try {
                                            if (!$record->mahasiswa->preferensi) {
                                                return 'Semua';
                                            }

                                            $preferensi = $record->mahasiswa->preferensi;

                                            $jenisMagangs = DB::table('m_jenis_magang')
                                                ->join('r_preferensi_jenis_magang', 'm_jenis_magang.id_jenis_magang', '=', 'r_preferensi_jenis_magang.id_jenis_magang')
                                                ->where('r_preferensi_jenis_magang.id_preferensi', $preferensi->id_preferensi)
                                                ->pluck('m_jenis_magang.nama_jenis_magang')
                                                ->toArray();

                                            if (empty($jenisMagangs)) {
                                                return 'Semua';
                                            }

                                            return implode(', ', $jenisMagangs);
                                        } catch (\Exception $e) {
                                            return 'Semua: ' . $e->getMessage();
                                        }
                                    }),

                                TextEntry::make('bidangMahasiswa')
                                    ->label('Bidang Keahlian')
                                    ->default('Semua')
                                    ->placeholder('Semua')
                                    ->getStateUsing(function ($record) {
                                        try {
                                            if (!$record->mahasiswa->preferensi) {
                                                return 'Semua';
                                            }

                                            $preferensi = $record->mahasiswa->preferensi;
                                            $bidangKeahlians = DB::table('m_bidang_keahlian')
                                                ->join('r_preferensi_bidang', 'm_bidang_keahlian.id_bidang', '=', 'r_preferensi_bidang.id_bidang')
                                                ->where('r_preferensi_bidang.id_preferensi', $preferensi->id_preferensi)
                                                ->pluck('m_bidang_keahlian.nama_bidang_keahlian')
                                                ->toArray();

                                            if (empty($bidangKeahlians)) {
                                                return 'Semua';
                                            }

                                            return implode(', ', $bidangKeahlians);
                                        } catch (\Exception $e) {
                                            return 'Semua: ' . $e->getMessage();
                                        }
                                    }),

                                TextEntry::make('mahasiswa.preferensi.insentif.keterangan')
                                    ->label('Insentif')
                                    ->default('Semua')
                                    ->placeholder('Semua'),

                                TextEntry::make('mahasiswa.preferensi.waktuMagang.waktu_magang')
                                    ->label('Waktu Magang')
                                    ->default('Semua')
                                    ->placeholder('Semua'),

                            ])->columns(2)
                            ->collapsible(),

                        Section::make('Dokumen Mahasiswa')
                            ->schema([
                                RepeatableEntry::make('mahasiswa.dokumen')
                                    ->schema([
                                        TextEntry::make('jenis_dokumen')
                                            ->label('Jenis Dokumen'),

                                        TextEntry::make('path_dokumen')
                                            ->label('Lihat Dokumen')
                                            ->color('primary')
                                            ->formatStateUsing(function ($state, $record) {
                                                $extension = pathinfo($state, PATHINFO_EXTENSION);
                                                return $record->nama_dokumen . '.' . $extension;
                                            })
                                            ->url(fn($record) => asset('storage/' . $record->path_dokumen), true)
                                            ->openUrlInNewTab(),
                                    ])
                                    ->columns(2),

                                TextEntry::make('dokumenEmpty')
                                    ->label('')
                                    ->default('Mahasiswa belum mengunggah dokumen apapun')
                                    ->visible(function ($record) {
                                        return !$record->mahasiswa->dokumen || $record->mahasiswa->dokumen->isEmpty();
                                    }),
                            ])
                            // ->columns(2)
                            ->collapsible(),
                    ])->columns(3),

                Section::make('Informasi Perusahaan')
                    ->schema([
                        TextEntry::make('lowongan.perusahaan.nama')
                            ->label('Perusahaan'),

                        TextEntry::make('lowongan.perusahaan.no_telepon')
                            ->label('Nomor Telepon Perusahaan')
                            ->icon('heroicon-m-phone'),

                        TextEntry::make('lowongan.perusahaan.website')
                            ->label('Website Perusahaan')
                            ->icon('heroicon-m-globe-alt'),

                        TextEntry::make('lowongan.perusahaan.partnership')
                            ->label('Status Perusahaan')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'Perusahaan Mitra' => 'success',
                                'Perusahaan Non-Mitra' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('lowongan.perusahaan.alamat')
                            ->label('Alamat Perusahaan')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Detail Magang')
                    ->schema([
                        TextEntry::make('lowongan.judul_lowongan')
                            ->label('Judul Lowongan'),

                        TextEntry::make('lowongan.jenisMagang.nama_jenis_magang')
                            ->label('Jenis Magang'),

                        TextEntry::make('lowongan.daerahMagang.provinsi.nama_provinsi')
                            ->label('Provinsi'),

                        TextEntry::make('lowongan.daerahMagang.namaLengkap')
                            ->label('Daerah (Kota/Kabupaten)'),

                        TextEntry::make('lowongan.periode.nama_periode')
                            ->label('Periode'),

                        TextEntry::make('lowongan.waktuMagang.waktu_magang')
                            ->label('Waktu Magang'),

                        TextEntry::make('lowongan.insentif.keterangan')
                            ->label('Insentif'),


                        TextEntry::make('lowongan.status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'Aktif' => 'success',
                                'Selesai' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('lowongan.tanggal_posting')
                            ->label('Tanggal Posting')
                            ->date(),

                        TextEntry::make('lowongan.batas_akhir_lamaran')
                            ->label('Batas Akhir Lamaran')
                            ->date(),

                        TextEntry::make('lowongan.deskripsi_lowongan')
                            ->label('Deskripsi Lowongan')
                            ->html()
                            ->columnSpanFull(),
                    ])->columns(3),

                Section::make('Dosen Pembimbing')
                    ->schema([
                        TextEntry::make('penempatan.dosenPembimbing.user.nama')
                            ->label('Nama Dosen Pembimbing'),

                        TextEntry::make('penempatan.dosenPembimbing.nip')
                            ->label('NIP Dosen Pembimbing'),

                        TextEntry::make('penempatan.dosenPembimbing.user.no_telepon')
                            ->label('Nomor Telepon Dosen Pembimbing')
                            ->icon('heroicon-m-phone'),

                        TextEntry::make('penempatan.dosenPembimbing.bidangKeahlian.nama_bidang_keahlian')
                            ->label('Bidang Keahlian Dosen Pembimbing'),
                    ])
                    ->columns(2)
                    ->visible(fn($record) => $record->status === 'Diterima'),

                Section::make('Catatan Penolakan')
                    ->schema([
                        TextEntry::make('alasan_penolakan')
                            ->label('Alasan Penolakan')
                            ->html()
                            ->state(function ($record) {
                                if (empty($record->alasan_penolakan)) {
                                    return 'Tidak memenuhi syarat atau alasan lainnya.';
                                }

                                return $record->alasan_penolakan;
                            }),
                    ])->columns(1)
                    ->visible(fn($record) => $record->status === 'Ditolak'),
            ]);
    }
}
