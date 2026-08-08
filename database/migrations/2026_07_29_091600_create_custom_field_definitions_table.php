<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('key'); // slug used inside the leads.custom_fields JSON
            $table->string('label');
            $table->string('type', 20)->default('text'); // text, number, date
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['client_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_definitions');
    }
};
