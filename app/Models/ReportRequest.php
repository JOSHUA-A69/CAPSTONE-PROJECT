<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportRequest extends Model
{
    protected $table = 'report_requests';

    protected $fillable = [
        'user_id', 'type', 'format', 'filters', 'status', 'file_path', 'rows_count', 'error',
    ];

    protected $casts = [
        'filters' => 'array',
        'rows_count' => 'integer',
    ];
}
