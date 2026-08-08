<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(){Schema::create('clients',function(Blueprint $t){$t->id();$t->string('client_code')->unique();$t->string('company_name');$t->string('owner_name');$t->string('email')->unique();$t->string('phone')->nullable();$t->enum('status',['active','inactive'])->default('active');$t->softDeletes();$t->timestamps();});}
 public function down(){Schema::dropIfExists('clients');}
};