<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(){Schema::create('audit_logs',function(Blueprint $t){
     $t->id();
     $t->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
     $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
     $t->string('action');
     $t->string('auditable_type')->nullable();
     $t->unsignedBigInteger('auditable_id')->nullable();
     $t->string('description')->nullable();
     $t->json('meta')->nullable();
     $t->string('ip_address', 45)->nullable();
     $t->timestamp('created_at')->nullable();
     $t->index(['client_id','created_at']);
     $t->index(['auditable_type','auditable_id']);
 });}
 public function down(){Schema::dropIfExists('audit_logs');}
};
