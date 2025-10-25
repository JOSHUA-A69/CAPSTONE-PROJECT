<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $first_name
 * @property string|null $middle_name
 * @property string $last_name
 * @property string $email
 * @property string|null $phone
 * @property string $role
 * @property string $status
 * @property int|null $user_role_id
 * @property \Carbon\Carbon|null $email_verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read string $full_name
 * @property-read string|null $name (legacy accessor, use first_name instead)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Organization> $organizations
 */
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
        'profile_picture',
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
     * Convenience accessor for full name.
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->first_name ?? null,
            $this->middle_name ?? null,
            $this->last_name ?? null
        ]);
        return implode(' ', $parts) ?: 'Unknown User';
    }

    /**
     * Legacy accessor for 'name' property (returns first_name).
     */
    public function getNameAttribute(): ?string
    {
        return $this->first_name;
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

    /**
     * Get the profile picture URL with default fallback
     */
    public function getProfilePictureUrlAttribute(): string
    {
        if ($this->profile_picture && file_exists(public_path('storage/' . $this->profile_picture))) {
            return asset('storage/' . $this->profile_picture);
        }

        // Generate default avatar with initials using UI Avatars service
        $name = urlencode($this->full_name);
        return "https://ui-avatars.com/api/?name={$name}&size=200&background=4F46E5&color=ffffff&bold=true";
    }

    /**
     * Get user initials for avatar
     */
    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', $this->full_name);
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
        }
        return strtoupper(substr($this->full_name, 0, 2));
    }
}

