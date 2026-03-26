<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

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
        'login_code',
        'login_code_expires_at',
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
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'name',
        'full_name',
        'profile_picture_url',
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
        $nameParts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ]);
        
        return implode(' ', $nameParts) ?: null;
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
        try {
            if ($this->profile_picture) {
                return \Illuminate\Support\Facades\Storage::url($this->profile_picture);
            }
        } catch (\Throwable $e) {
            // Fallback if Cloudinary config is invalid/missing
            return asset('images/default-avatar.svg');
        }

        // Professional default avatar (local asset)
        return asset('images/default-avatar.svg');
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

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\Auth\ResetPasswordNotification($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\Auth\VerifyEmailNotification);
    }
}

