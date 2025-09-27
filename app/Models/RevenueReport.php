<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueReport extends Model
{
    use HasFactory;

    protected $table = 'revenue_reports';
    protected $primaryKey = 'report_id';

    protected $fillable = [
        'date',
        'total_orders',
        'total_revenue',
        'total_profit',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
