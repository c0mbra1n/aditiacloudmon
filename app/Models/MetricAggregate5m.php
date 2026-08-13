<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetricAggregate5m extends Model
{
    use HasFactory;

    protected $table = 'metric_aggregates_5m';

    protected $fillable = [
        'server_id',
        'avg_cpu',
        'max_cpu',
        'avg_ram',
        'max_ram',
        'avg_disk',
        'max_disk',
        'sample_count',
        'bucket_at',
    ];

    protected $casts = [
        'bucket_at' => 'datetime',
        'avg_cpu' => 'double',
        'max_cpu' => 'double',
        'avg_ram' => 'double',
        'max_ram' => 'double',
        'avg_disk' => 'double',
        'max_disk' => 'double',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
