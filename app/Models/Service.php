<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $service_id
 * @property string $service_name
 * @property string $service_category
 * @property string|null $description
 */
class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'services';
    protected $primaryKey = 'service_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'service_name',
        'service_category',
        'description',
        'duration',
    ];
}
