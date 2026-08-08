<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model {
 protected $fillable=['lead_id','assigned_to','follow_up_at','type','subject','notes','status','completed_at','completed_by','created_by','reminded_at'];
 protected $casts=['follow_up_at'=>'datetime','completed_at'=>'datetime','reminded_at'=>'datetime'];
 public function lead(){return $this->belongsTo(Lead::class);}
 public function assignee(){return $this->belongsTo(User::class,'assigned_to');}
}