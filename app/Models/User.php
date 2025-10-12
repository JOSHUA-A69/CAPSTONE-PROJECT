<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'phone',
        'role',
        'status',
        'user_role_id',
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
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Model default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'requestor',
    ];

    /**
     * Convenience accessor for full name if `name` is not used.
     */
    public function getFullNameAttribute(): string
    {
        if (! empty($this->name)) {
            return $this->name;
        }

        $parts = array_filter([$this->first_name ?? null, $this->middle_name ?? null, $this->last_name ?? null]);
        return implode(' ', $parts);
    }

    /**
     * Relationship to UserRole model.
     */
    public function userRole()
    {
        return $this->belongsTo(UserRole::class, 'user_role_id', 'user_role_id');
    }

    /**
     * Organizations where this user is set as adviser.
     */
    public function organizations()
    {
        return $this->hasMany(Organization::class, 'adviser_id', 'id');
    }
}
