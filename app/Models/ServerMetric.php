<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServerMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'cpu_usage_percent',
        'ram_total_bytes',
        'ram_used_bytes',
        'ram_usage_percent',
        'uptime_seconds',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function disks(): HasMany
    {
        return $this->hasMany(ServerDisk::class);
    }

    public function networks(): HasMany
    {
        return $this->hasMany(ServerNetwork::class);
    }
}
