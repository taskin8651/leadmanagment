<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(){Schema::create('follow_ups',function(Blueprint $t){$t->id();$t->foreignId('lead_id')->constrained()->cascadeOnDelete();$t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();$t->dateTime('follow_up_at');$t->string('type')->default('Call');$t->string('subject')->nullable();$t->text('notes')->nullable();$t->enum('status',['pending','completed','cancelled','missed'])->default('pending');$t->dateTime('completed_at')->nullable();$t->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();$t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();$t->index(['status','follow_up_at']);});}
 public function down(){Schema::dropIfExists('follow_ups');}
};