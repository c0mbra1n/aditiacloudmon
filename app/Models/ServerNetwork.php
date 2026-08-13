<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerNetwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_metric_id',
        'server_id',
        'interface_name',
        'ip_address',
        'bytes_sent_per_sec',
        'bytes_recv_per_sec',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
