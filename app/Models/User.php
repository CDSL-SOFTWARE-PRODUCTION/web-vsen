<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        // Admin_PM (Founder) có quyền truy cập toàn bộ hệ thống
        if ($this->role === 'Admin_PM') {
            return true;
        }

        // Panel CMS: Chỉ dành cho Admin_PM (hoặc Marketing nếu bổ sung sau)
        if ($panel->getId() === 'cms') {
            return $this->role === 'Admin_PM';
        }

        // Panel Ops (Business OS): Dành cho các bộ phận chuyên môn
        if ($panel->getId() === 'ops') {
            return in_array($this->role, [
                'Sale',
                'MuaHang',
                'Kho',
                'KeToan',
                'Admin_PM',
            ]);
        }

        if ($panel->getId() === 'data-steward') {
            return in_array($this->role, [
                'DuLieuNen',
                'Admin_PM',
            ]);
        }

        return false;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'legal_entity_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'legal_entity_id' => 'integer',
        ];
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }
}
