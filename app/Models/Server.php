<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Server extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'hostname',
        'ip_address',
        'os_name',
        'os_version',
        'agent_version',
        'cpu_model',
        'cpu_cores',
        'ram_total_bytes',
        'status',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'ram_total_bytes' => 'integer',
        'cpu_cores' => 'integer',
    ];

    public function agent(): HasOne
    {
        return $this->hasOne(Agent::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(ServerService::class);
    }

    public function processes(): HasMany
    {
        return $this->hasMany(ServerProcess::class);
    }

    public function ports(): HasMany
    {
        return $this->hasMany(ServerPort::class);
    }

    public function alertRules(): HasMany
    {
        return $this->hasMany(AlertRule::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(AgentCommand::class);
    }
}
