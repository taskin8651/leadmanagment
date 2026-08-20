<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(){Schema::table('follow_ups',function(Blueprint $t){$t->unsignedInteger('reschedule_count')->default(0)->after('status');});}
 public function down(){Schema::table('follow_ups',function(Blueprint $t){$t->dropColumn('reschedule_count');});}
};
