<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerDisk extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_metric_id',
        'server_id',
        'drive_letter',
        'label',
        'filesystem',
        'total_bytes',
        'free_bytes',
        'used_bytes',
        'usage_percent',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
