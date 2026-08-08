<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(){
     Schema::table('invoices',function(Blueprint $t){
         $t->string('customer_gstin')->nullable()->after('customer_address');
         $t->string('place_of_supply')->nullable()->after('customer_gstin');
         $t->boolean('is_interstate')->default(false)->after('place_of_supply');
         $t->decimal('cgst_amount',12,2)->default(0)->after('tax_amount');
         $t->decimal('sgst_amount',12,2)->default(0)->after('cgst_amount');
         $t->decimal('igst_amount',12,2)->default(0)->after('sgst_amount');
     });
     Schema::table('invoice_items',function(Blueprint $t){
         $t->string('hsn_code')->nullable()->after('description');
     });
 }
 public function down(){
     Schema::table('invoices',function(Blueprint $t){
         $t->dropColumn(['customer_gstin','place_of_supply','is_interstate','cgst_amount','sgst_amount','igst_amount']);
     });
     Schema::table('invoice_items',function(Blueprint $t){
         $t->dropColumn('hsn_code');
     });
 }
};
