<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model {
 use SoftDeletes;
 protected $fillable=['client_code','company_name','owner_name','email','phone','status','address','gstin','logo_path','payment_details'];
 public function leads(){return $this->hasMany(Lead::class);}
 public function user(){return $this->hasOne(User::class)->whereHas('roles', fn($q) => $q->where('name', 'Admin'));}
 public function staff(){return $this->hasMany(User::class)->whereHas('roles', fn($q) => $q->whereIn('name', ['Staff', 'Telecaller']));}
 public function tags(){return $this->hasMany(Tag::class);}
 public function attendances(){return $this->hasMany(Attendance::class);}
 public function customFieldDefinitions(){return $this->hasMany(CustomFieldDefinition::class)->orderBy('sort_order');}
 public function invoices(){return $this->hasMany(Invoice::class);}

 public function nextAssignee()
 {
     return $this->staff()->where('is_active', true)
         ->withCount(['assignedLeads' => fn ($q) => $q->where('client_id', $this->id)])
         ->orderBy('assigned_leads_count')
         ->first();
 }

 public function canAccess(): bool {
   return $this->status === 'active';
 }
}