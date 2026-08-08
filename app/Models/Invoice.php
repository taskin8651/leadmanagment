<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model {
 use SoftDeletes;
 protected $fillable=['uuid','invoice_number','client_id','lead_id','customer_name','customer_phone','customer_email','customer_address','customer_gstin','place_of_supply','is_interstate','issue_date','due_date','subtotal','discount','tax_percent','tax_amount','cgst_amount','sgst_amount','igst_amount','total','status','notes','created_by'];
 protected $casts=['issue_date'=>'date','due_date'=>'date','is_interstate'=>'boolean','subtotal'=>'decimal:2','discount'=>'decimal:2','tax_percent'=>'decimal:2','tax_amount'=>'decimal:2','cgst_amount'=>'decimal:2','sgst_amount'=>'decimal:2','igst_amount'=>'decimal:2','total'=>'decimal:2'];
 public function getRouteKeyName(){return 'uuid';}
 public function client(){return $this->belongsTo(Client::class);}
 public function lead(){return $this->belongsTo(Lead::class);}
 public function creator(){return $this->belongsTo(User::class,'created_by');}
 public function items(){return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');}

 public function recalculateTotals(): void
 {
     $subtotal = $this->items->sum(fn ($item) => $item->quantity * $item->rate);
     $taxable = max(0, $subtotal - $this->discount);
     $taxAmount = round($taxable * $this->tax_percent / 100, 2);

     $cgst = $sgst = $igst = 0;
     if ($this->is_interstate) {
         $igst = $taxAmount;
     } else {
         $cgst = $sgst = round($taxAmount / 2, 2);
     }

     $this->forceFill([
         'subtotal' => $subtotal,
         'tax_amount' => $taxAmount,
         'cgst_amount' => $cgst,
         'sgst_amount' => $sgst,
         'igst_amount' => $igst,
         'total' => $taxable + $taxAmount,
     ])->save();
 }
}
