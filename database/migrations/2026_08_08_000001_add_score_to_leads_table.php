<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(){Schema::table('leads',function(Blueprint $t){$t->unsignedTinyInteger('score')->default(0)->after('priority');$t->index('score');});}
 public function down(){Schema::table('leads',function(Blueprint $t){$t->dropIndex(['score']);$t->dropColumn('score');});}
};
