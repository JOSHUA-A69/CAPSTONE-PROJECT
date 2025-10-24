<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Temporary placeholder model for normalized reservation details.
 * This can be expanded or removed if you decide to fully repurpose
 * existing event assignment/role tables instead.
 */
class ReservationDetail extends Model
{
    use HasFactory;

    protected $table = 'reservation_details';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'reservation_id',
        // add normalized service-specific fields here if/when used
    ];
}
