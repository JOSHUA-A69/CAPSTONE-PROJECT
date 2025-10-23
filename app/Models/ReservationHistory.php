<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationHistory extends Model
{
    use HasFactory;

    protected $table = 'reservation_history';
    protected $primaryKey = 'history_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'reservation_id', 'performed_by', 'action', 'remarks', 'performed_at',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'reservation_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by', 'id');
    }
}
