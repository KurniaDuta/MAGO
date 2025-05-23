<?php

namespace App\Models;

use App\Models\Auth\AdminModel;
use App\Models\Auth\DosenPembimbingModel;
use App\Models\Auth\MahasiswaModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;

class UserModel extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, HasRoles;

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // atau bisa pakai role check
    }
    
    public function getFilamentName(): string
    {
        return $this->nama;
    }

    // m_user 
    // + id_user: int (PK)
    // + nama: string
    // + password: string
    // + alamat: string
    // + no_telepon: string

    protected $table = 'm_user';
    protected $primaryKey = 'id_user';
    protected $fillable = [
        'nama',
        'password',
        'alamat',
        'no_telepon',
        'role',
    ];
    protected $hidden = [
        'password',
    ];
    protected $casts = [
        'role' => 'string',
    ];
    public $timestamps = true;

    public function isAdmin() { return $this->role === 'admin'; }
    public function isMahasiswa() { return $this->role === 'mahasiswa'; }
    public function isDosen() { return $this->role === 'dosen_pembimbing'; }
 
    public function mahasiswa()
    {
        return $this->hasOne(MahasiswaModel::class, 'id_user');
    }

    public function dosenPembimbing()
    {
        return $this->hasOne(DosenPembimbingModel::class, 'id_user');
    }

    public function admin()
    {
        return $this->hasOne(AdminModel::class, 'id_user');
    }
}
