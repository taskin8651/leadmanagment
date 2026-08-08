<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(){Schema::create('leads',function(Blueprint $t){$t->id();$t->string('lead_number')->unique();$t->string('name');$t->string('company_name')->nullable();$t->string('email')->nullable();$t->string('phone');$t->string('source')->default('Other');$t->enum('status',['new','contacted','qualified','follow-up','won','lost'])->default('new');$t->enum('priority',['low','medium','high','hot'])->default('medium');$t->foreignId('client_id')->nullable()->constrained()->nullOnDelete();$t->decimal('estimated_value',12,2)->nullable();$t->dateTime('next_follow_up_at')->nullable();$t->text('notes')->nullable();$t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();$t->softDeletes();$t->timestamps();$t->index(['status','priority']);$t->index('phone');});}
 public function down(){Schema::dropIfExists('leads');}
};