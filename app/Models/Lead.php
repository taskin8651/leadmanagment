<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model {
 use SoftDeletes;
 protected $fillable=['lead_number','name','company_name','email','phone','source','status','priority','score','client_id','assigned_to','estimated_value','next_follow_up_at','notes','created_by','custom_fields','first_response_at','sla_alerted_at'];

 const SOURCE_WEIGHTS = [
     'Referral' => 25, 'Website' => 20, 'WhatsApp Ads' => 20, 'Walk-in' => 20,
     'Google Ads' => 15, 'Facebook Ads' => 15, 'Facebook Lead Ads' => 15, 'Google Ads Lead Form' => 15,
 ];
 protected $casts=['estimated_value'=>'decimal:2','next_follow_up_at'=>'datetime','custom_fields'=>'array','first_response_at'=>'datetime','sla_alerted_at'=>'datetime'];
 public function client(){return $this->belongsTo(Client::class);}
 public function followUps(){return $this->hasMany(FollowUp::class);}
 public function creator(){return $this->belongsTo(User::class,'created_by');}
 public function assignee(){return $this->belongsTo(User::class,'assigned_to');}
 public function activities(){return $this->hasMany(LeadActivity::class)->latest();}
 public function tags(){return $this->belongsToMany(Tag::class);}
 public function attachments(){return $this->hasMany(LeadAttachment::class)->latest();}
 public function invoices(){return $this->hasMany(Invoice::class)->latest('issue_date');}

 public function logActivity(string $type, ?string $description = null, array $meta = [], ?int $userId = null): LeadActivity
 {
     $activity = $this->activities()->create([
         'user_id' => $userId ?? auth()->id(),
         'type' => $type,
         'description' => $description,
         'meta' => $meta,
     ]);
     $this->recalculateScore();
     return $activity;
 }

 public function computeScore(): int
 {
     $score = self::SOURCE_WEIGHTS[$this->source] ?? 10;

     if ($this->estimated_value >= 100000) $score += 25;
     elseif ($this->estimated_value >= 50000) $score += 15;
     elseif ($this->estimated_value >= 10000) $score += 8;

     $engagementTypes = ['call', 'whatsapp', 'email', 'note'];
     $engagements = $this->exists ? $this->activities()->whereIn('type', $engagementTypes)->count() : 0;
     $score += min(25, $engagements * 5);

     if ($this->first_response_at && $this->created_at) {
         $minutes = $this->created_at->diffInMinutes($this->first_response_at);
         if ($minutes <= 60) $score += 15;
         elseif ($minutes <= 1440) $score += 8;
     }

     if ($this->next_follow_up_at && $this->next_follow_up_at->isFuture()) $score += 10;

     return min(100, $score);
 }

 public function recalculateScore(): void
 {
     $score = $this->computeScore();
     if ($score !== $this->score) {
         $this->forceFill(['score' => $score])->saveQuietly();
     }
 }

 public function suggestedPriority(): string
 {
     return match (true) {
         $this->score >= 70 => 'hot',
         $this->score >= 50 => 'high',
         $this->score >= 25 => 'medium',
         default => 'low',
     };
 }

 public function markFirstResponse(): void
 {
     if (!$this->first_response_at) {
         $this->forceFill(['first_response_at' => now()])->save();
     }
 }

 public function duplicates()
 {
     return static::where('client_id', $this->client_id)
         ->where('id', '!=', $this->id)
         ->where('phone', $this->phone)
         ->get();
 }
}
