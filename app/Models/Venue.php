<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $venue_id
 * @property string $name
 * @property int|null $capacity
 * @property string|null $location
 */
class Venue extends Model
{
    use HasFactory;

    protected $table = 'venues';
    protected $primaryKey = 'venue_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name', 'capacity', 'location',
    ];
}
