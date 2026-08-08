<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldDefinition extends Model
{
    protected $fillable = ['client_id', 'key', 'label', 'type', 'sort_order'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
