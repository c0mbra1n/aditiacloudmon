<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'process_name',
        'pid',
        'cpu_percent',
        'memory_bytes',
        'status',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
