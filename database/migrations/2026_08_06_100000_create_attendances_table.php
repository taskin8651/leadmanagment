<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(){Schema::create('attendances',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->foreignId('client_id')->constrained()->cascadeOnDelete();$t->date('date');$t->dateTime('check_in_at')->nullable();$t->decimal('check_in_latitude',10,7)->nullable();$t->decimal('check_in_longitude',10,7)->nullable();$t->string('check_in_address')->nullable();$t->dateTime('check_out_at')->nullable();$t->decimal('check_out_latitude',10,7)->nullable();$t->decimal('check_out_longitude',10,7)->nullable();$t->string('check_out_address')->nullable();$t->enum('status',['present','late','half_day'])->default('present');$t->text('notes')->nullable();$t->timestamps();$t->index(['client_id','date']);$t->index(['user_id','date']);});}
 public function down(){Schema::dropIfExists('attendances');}
};
