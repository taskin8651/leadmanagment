<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id', 'client_id', 'date',
        'check_in_at', 'check_in_latitude', 'check_in_longitude', 'check_in_address',
        'check_out_at', 'check_out_latitude', 'check_out_longitude', 'check_out_address',
        'status', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'check_in_latitude' => 'float',
        'check_in_longitude' => 'float',
        'check_out_latitude' => 'float',
        'check_out_longitude' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getWorkedMinutesAttribute(): ?int
    {
        if (!$this->check_in_at || !$this->check_out_at) return null;
        return $this->check_in_at->diffInMinutes($this->check_out_at);
    }
}
