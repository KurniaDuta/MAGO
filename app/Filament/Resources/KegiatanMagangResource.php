<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KegiatanMagangResource\Pages;
use App\Filament\Resources\KegiatanMagangResource\RelationManagers;
use App\Models\KegiatanMagang;
use App\Models\Reference\PengajuanMagangModel;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class KegiatanMagangResource extends Resource
{
    protected static ?string $model = PengajuanMagangModel::class;

    protected static ?string $navigationIcon = 'heroicon-s-document-check';
    protected static ?string $navigationLabel = 'Pengajuan & Lamaran Magang';
    protected static ?string $slug = 'kegiatan-magang';
    protected static ?string $modelLabel = 'Pengajuan Magang';
    protected static ?string $pluralModelLabel = 'Data Pengajuan & Lamaran Magang';
    protected static ?string $navigationGroup = 'Administrasi Magang';
    protected static ?int $navigationSort = 1;


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.user.nama')
                    ->label('Nama Mahasiswa')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('lowongan.judul_lowongan')
                    ->label('Judul Lowongan')
                    ->limit(20)
                    ->copyable()
                    ->tooltip(fn($record) => $record->lowongan->judul_lowongan)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('lowongan.jenisMagang.nama_jenis_magang')
                    ->label('Jenis Magang')
                    ->limit(15)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_pengajuan')
                    ->label('Tanggal Pengajuan')
                    ->sortable()
                    ->date(),

                Tables\Columns\TextColumn::make('tanggal_diterima')
                    ->label('Tanggal Diterima')
                    ->sortable()
                    ->date(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->colors([
                        'warning' => 'Diajukan',
                        'success' => 'Diterima',
                        'danger' => 'Ditolak',
                    ])
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('tanggal_pengajuan', 'desc')
            ->emptyStateHeading('Belum ada kegiatan magang yang diajukan')
            ->emptyStateIcon('heroicon-o-document-text')
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_magang')
                    ->label('Jenis Magang')
                    ->relationship('lowongan.jenisMagang', 'nama_jenis_magang')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Diajukan' => 'Diajukan',
                        'Diterima' => 'Diterima',
                        'Ditolak' => 'Ditolak',
                    ]),

                Tables\Filters\SelectFilter::make('perusahaan')
                    ->label('Perusahaan')
                    ->relationship('lowongan.perusahaan', 'nama')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('periode')
                    ->label('Periode')
                    ->relationship('lowongan.periode', 'nama_periode')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Pengajuan')
                    ->icon('heroicon-o-eye')
                    ->tooltip('Lihat detail pengajuan magang'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('Hapus Pengajuan Magang')
                        ->modalDescription('Apakah Anda yakin ingin menghapus pengajuan magang ini? Pengajuan dengan status Diterima tidak akan dihapus.')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal')
                        ->deselectRecordsAfterCompletion()
                        ->before(function ($records) {
                            $cannotDeleteRecords = [];

                            foreach ($records as $key => $record) {
                                if ($record->status === 'Diterima') {
                                    $cannotDeleteRecords[] = $record->mahasiswa->user->nama ?? ('Pengajuan #' . $record->id_pengajuan);
                                    $records->forget($records->search($record));
                                }
                            }

                            if (count($cannotDeleteRecords) > 0) {
                                Notification::make()
                                    ->warning()
                                    ->title('Perhatian')
                                    ->body('Beberapa pengajuan magang dengan status Diterima tidak dapat dihapus: ' . implode(', ', $cannotDeleteRecords))
                                    ->send();
                            }

                            if ($records->isEmpty()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Gagal')
                                    ->body('Semua pengajuan yang dipilih memiliki status Diterima dan tidak dapat dihapus.')
                                    ->send();
                                return false;
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKegiatanMagangs::route('/'),
            'view' => Pages\ViewKegiatanMagang::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['mahasiswa.user', 'mahasiswa.prodi', 'lowongan.perusahaan', 'lowongan.bidangKeahlian', 'mahasiswa.dokumen']);
    }
}
