<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
     use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'f_name',
        'l_name',
        'enforcer_id',
        'username',
        'email',
        'phone',
        'assigned_area',
        'role_id',
        'status_id',
        'password',
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
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function status()
    {
        return $this->belongsTo(UserStatus::class, 'status_id');
    }

    public function details()
    {
        return $this->hasOne(UserDetail::class);
    }

    public function clampings()
    {
        return $this->hasMany(Clamping::class);
    }

    public function devices()
    {
        return $this->hasMany(DeviceManager::class);
    }

    public function generateEnforcerId()
    {
        $role = $this->role;
        
        if (!$role) {
            return null;
        }

        $prefix = match(strtolower($role->name)) {
            'admin' => 'AD',
            'enforcer' => 'ENF',
            'front desk' => 'FD',
            default => 'US',
        };

        return $prefix . '-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }
}