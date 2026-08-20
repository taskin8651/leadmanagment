<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    protected $fillable = ['lead_id', 'user_id', 'type', 'description', 'meta'];
    protected $casts = ['meta' => 'array'];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public const ICONS = [
        'created' => 'bi-flag',
        'note' => 'bi-sticky',
        'call' => 'bi-telephone',
        'whatsapp' => 'bi-whatsapp',
        'email' => 'bi-envelope',
        'status_change' => 'bi-arrow-left-right',
        'assigned' => 'bi-person-check',
        'follow_up_completed' => 'bi-check2-circle',
        'follow_up_rescheduled' => 'bi-arrow-repeat',
        'tag' => 'bi-tag',
    ];

    public function getIconAttribute(): string
    {
        return self::ICONS[$this->type] ?? 'bi-dot';
    }
}
