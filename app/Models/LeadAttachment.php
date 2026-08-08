<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadAttachment extends Model
{
    protected $fillable = ['lead_id', 'uploaded_by', 'original_name', 'path', 'mime', 'size'];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
