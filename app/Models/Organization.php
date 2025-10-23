<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $org_id
 * @property int $adviser_id
 * @property string $org_name
 * @property string|null $org_desc
 *
 * @property-read User $adviser
 */
class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';
    protected $primaryKey = 'org_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'adviser_id',
        'org_name',
        'org_desc',
    ];

    public function adviser()
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }
}
