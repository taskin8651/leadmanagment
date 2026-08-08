<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->text('address')->nullable()->after('phone');
            $table->string('gstin')->nullable()->after('address');
            $table->string('logo_path')->nullable()->after('gstin');
            $table->text('payment_details')->nullable()->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['address', 'gstin', 'logo_path', 'payment_details']);
        });
    }
};
