<?php

namespace App\Filament\Pembimbing\Resources;

use App\Filament\Pembimbing\Resources\MonitoringaktivitasMagangResource\Pages;
use App\Models\Reference\LogMagangModel;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class MonitoringaktivitasMagangResource extends Resource
{
    protected static ?string $model = LogMagangModel::class;

    protected static ?string $navigationLabel = 'Monitoring Magang';
    protected static ?string $navigationIcon = 'heroicon-s-user-group';
    protected static ?string $modelLabel = 'Log Aktivitas';
    protected static ?string $pluralModelLabel = 'Monitoring Aktivitas';
    protected static ?string $navigationGroup = 'Monitoring & Mahasiswa';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $dosen = $user->dosenPembimbing;

        if (!$dosen) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        $penempatanIds = $dosen->mahasiswaBimbingan()
            ->pluck('t_penempatan_magang.id_penempatan')
            ->toArray();

        return parent::getEloquentQuery()
            ->whereIn('id_penempatan', $penempatanIds)
            ->with([
                'penempatan.mahasiswa.user',
                'penempatan.pengajuan.lowongan.perusahaan'
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('file_bukti')
                    ->label('Dokumentasi')
                    ->height(60)
                    ->width(60)
                    ->square()
                    ->grow(false)
                    ->getStateUsing(function ($record) {
                        return $record->file_bukti
                            ? 'https://res.cloudinary.com/dxwwjhtup/image/upload/' . ltrim($record->file_bukti, '/')
                            : null;
                    })
                    ->extraImgAttributes([
                        'class' => 'object-cover border border-gray-200 rounded-md',
                        'style' => 'aspect-ratio: 1/1'
                    ]),

                Tables\Columns\TextColumn::make('penempatan.mahasiswa.user.nama')
                    ->label('Mahasiswa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_log')
                    ->label('Tanggal')
                    ->date('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Aktivitas')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('status')
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

                Tables\Columns\TextColumn::make('feedback_progres')
                    ->label('Feedback')
                    ->formatStateUsing(function ($state) {
                        return $state ?: '🔄 BELUM ADA FEEDBACK';
                    })
                    ->color(fn($state) => $state ? 'gray' : 'danger')
                    ->weight(fn($state) => $state ? null : 'bold')
                    ->limit(25)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 25 ? $state : null;
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_penempatan')
                    ->label('Filter Mahasiswa')
                    ->searchable()
                    ->options(function () {
                        $user = auth()->user();
                        $dosen = $user->dosenPembimbing;

                        if (!$dosen) return [];

                        return $dosen->mahasiswaBimbingan()
                            ->with('mahasiswa.user')
                            ->get()
                            ->filter(fn($penempatan) => $penempatan->mahasiswa && $penempatan->mahasiswa->user)
                            ->mapWithKeys(function ($penempatan) {
                                return [
                                    $penempatan->id_penempatan => $penempatan->mahasiswa->user->nama,
                                ];
                            })
                            ->toArray();
                    })
                    ->placeholder('Pilih Mahasiswa'),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Kehadiran')
                    ->options([
                        'masuk' => 'Masuk',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'cuti' => 'Cuti',
                    ])
                    ->placeholder('Semua Status'),

                Tables\Filters\Filter::make('tanpa_feedback')
                    ->label('Belum Diberi Feedback')
                    ->query(fn(Builder $query) => $query->whereNull('feedback_progres'))
                    ->default()
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail Aktivitas')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
            ])
            ->bulkActions([])
            ->defaultSort('tanggal_log', 'desc')
            ->emptyStateHeading('Belum Ada Aktivitas Magang')
            ->emptyStateDescription('Mahasiswa bimbingan Anda belum melaporkan aktivitas magang.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonitoringaktivitasMagangs::route('/'),
            'edit' => Pages\EditMonitoringaktivitasMagang::route('/{record}/edit'),
            'view' => Pages\ViewMonitoringaktivitasMagang::route('/{record}'),
        ];
    }
}
