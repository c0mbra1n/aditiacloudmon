<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlertRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'name',
        'metric_type',
        'target_name',
        'operator',
        'threshold_value',
        'severity',
        'duration_minutes',
        'cooldown_minutes',
        'is_enabled',
    ];

    protected $casts = [
        'threshold_value' => 'float',
        'duration_minutes' => 'integer',
        'cooldown_minutes' => 'integer',
        'is_enabled' => 'boolean',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
}
