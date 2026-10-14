<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'password_hash',
        'first_name',
        'last_name',
        'role_id',
        'is_active',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'password_hash',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login' => 'datetime',
        'is_active' => 'boolean',
    ];

    public $timestamps = false;
    
    protected static function booted(): void
    {
        static::creating(function (self $user) {
            if (!$user->username && $user->email) {
                $base = substr(preg_replace('/[^A-Za-z0-9_.]/', '.', Str::before($user->email, '@')) ?: 'staff', 0, 45);
                $username = $base;
                $suffix = 1;

                while (static::where('username', $username)->exists()) {
                    $username = substr($base, 0, 45 - strlen((string) $suffix)).$suffix;
                    $suffix++;
                }

                $user->username = $username;
            }

            if (!$user->first_name && $user->name) {
                $nameParts = preg_split('/\s+/', trim($user->name), 2);
                $user->first_name = $nameParts[0] ?? '';
                $user->last_name = $nameParts[1] ?? '';
            }

            $user->role_id ??= 2;
            $user->is_active ??= true;
            $user->password_hash ??= $user->password;
        });
    }

    public function getNameAttribute($value): string
    {
        return $value ?: trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }
}
