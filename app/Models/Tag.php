<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['client_id', 'name', 'color'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function leads()
    {
        return $this->belongsToMany(Lead::class);
    }
}
