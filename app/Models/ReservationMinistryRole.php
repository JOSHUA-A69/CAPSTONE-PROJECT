<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationMinistryRole extends Model
{
    use HasFactory;

    protected $table = 'reservation_ministry_roles';

    protected $fillable = [
        'reservation_id',
        'role',
        'name',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'reservation_id');
    }
}
