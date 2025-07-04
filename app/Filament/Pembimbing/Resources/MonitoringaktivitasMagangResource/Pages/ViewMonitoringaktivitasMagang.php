<?php

namespace App\Filament\Pembimbing\Resources\MonitoringaktivitasMagangResource\Pages;

use App\Filament\Pembimbing\Resources\MonitoringaktivitasMagangResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Carbon\Carbon;

class ViewMonitoringaktivitasMagang extends ViewRecord
{
    protected static string $resource = MonitoringaktivitasMagangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->url(MonitoringaktivitasMagangResource::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),

            Actions\Action::make('giveFeedback')
                ->label('Beri Feedback')
                ->color('primary')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->form([
                    Textarea::make('feedback_progres')
                        ->label('Feedback Dosen Pembimbing')
                        ->placeholder('Tuliskan feedback, arahan, atau koreksi untuk aktivitas yang dilakukan mahasiswa...')
                        ->rows(6)
                        ->required()
                        ->maxLength(500)
                        ->default(fn($record) => $record->feedback_progres),
                ])
                ->modalHeading('Berikan Feedback untuk Aktivitas Magang')
                ->modalDescription(fn($record) => 'Aktivitas pada tanggal ' . Carbon::parse($record->tanggal_log)->format('d F Y'))
                ->modalSubmitActionLabel('Simpan Feedback')
                ->action(function (array $data, $record) {
                    // Update data
                    $record->update([
                        'feedback_progres' => $data['feedback_progres'],
                    ]);

                    // Kirim notifikasi ke mahasiswa
                    $mahasiswa = $record->penempatan->mahasiswa->user ?? null;
                    if ($mahasiswa) {
                        Notification::make()
                            ->title('Feedback aktivitas magang diterima')
                            ->body('Dosen pembimbing telah memberikan feedback untuk aktivitas magang Anda pada tanggal ' .
                                Carbon::parse($record->tanggal_log)->format('d F Y'))
                            ->success()
                            ->persistent()
                            ->sendToDatabase($mahasiswa);
                    }

                    Notification::make()
                        ->title('Feedback berhasil disimpan')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Mahasiswa')
                    ->schema([
                        TextEntry::make('penempatan.mahasiswa.user.nama')
                            ->label('Nama Mahasiswa'),

                        TextEntry::make('penempatan.mahasiswa.nim')
                            ->label('NIM'),

                        TextEntry::make('penempatan.mahasiswa.prodi.nama_prodi')
                            ->label('Program Studi')
                            ->visible(fn($record) => (bool) ($record->penempatan->mahasiswa->prodi ?? false)),

                        TextEntry::make('penempatan.mahasiswa.semester')
                            ->label('Semester')
                            ->visible(fn($record) => (bool) ($record->penempatan->mahasiswa->semester ?? false)),

                        TextEntry::make('tanggal_log')
                            ->label('Tanggal Aktivitas')
                            ->date('d F Y'),

                        TextEntry::make('status')
                            ->label('Status Kehadiran')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => ucfirst($state))
                            ->color(fn(string $state): string => match ($state) {
                                'masuk' => 'success',
                                'izin' => 'warning',
                                'sakit' => 'danger',
                                'cuti' => 'info',
                                default => 'gray',
                            }),
                    ])->columns(3),

                Section::make('Informasi Perusahaan')
                    ->schema([
                        TextEntry::make('penempatan.pengajuan.lowongan.perusahaan.nama')
                            ->label('Perusahaan'),

                        TextEntry::make('penempatan.pengajuan.lowongan.judul_lowongan')
                            ->label('Posisi Magang'),

                        TextEntry::make('penempatan.pengajuan.lowongan.jenisMagang.nama_jenis_magang')
                            ->label('Jenis Magang'),

                        TextEntry::make('penempatan.pengajuan.lowongan.periode.nama_periode')
                            ->label('Periode Magang'),
                    ])->columns(2),

                Section::make('Detail Aktivitas')
                    ->schema([
                        ImageEntry::make('file_bukti')
                            ->getStateUsing(function ($record) {
                                return $record->file_bukti
                                    ? \Storage::disk('cloudinary')->url($record->file_bukti)
                                    : null;
                            })
                            ->height(400)
                            ->extraImgAttributes(['class' => 'rounded-lg object-contain shadow-sm']),

                        TextEntry::make('keterangan')
                            ->label('Keterangan Aktivitas')
                            ->prose()
                            ->markdown()
                            ->columnSpanFull(),
                    ]),

                // Section::make('Bukti Aktivitas')
                //     ->schema([])
                //     ->visible(fn($record) => !empty($record->file_bukti)),

                Section::make('Feedback Dosen')
                    ->schema([
                        TextEntry::make('feedback_progres')
                            ->label('Feedback')
                            ->prose()
                            ->markdown()
                            ->placeholder('Belum ada feedback')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(false),
            ]);
    }
}
