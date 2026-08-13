<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerPort extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'port',
        'protocol',
        'status',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
