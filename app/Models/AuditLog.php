<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['client_id', 'user_id', 'action', 'auditable_type', 'auditable_id', 'description', 'meta', 'ip_address', 'created_at'];

    protected $casts = ['meta' => 'array', 'created_at' => 'datetime'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, ?string $description = null, array $meta = [], $auditable = null): self
    {
        $user = Auth::user();

        return static::create([
            'client_id' => $user?->client_id,
            'user_id' => $user?->id,
            'action' => $action,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->id,
            'description' => $description,
            'meta' => $meta,
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);
    }
}
