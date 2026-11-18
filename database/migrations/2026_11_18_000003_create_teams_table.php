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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->string('name');
            $table->string('purok_zone')->nullable();
            $table->string('coach_name')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('primary_color', 20)->default('#ea580c');
            $table->string('secondary_color', 20)->default('#1e293b');
            $table->string('logo_path')->nullable();
            $table->timestamps();

            $table->unique(['tournament_id', 'name']);
            $table->index(['tenant_id', 'tournament_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
