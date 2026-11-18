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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('jersey_number', 3);
            $table->string('position', 5);
            $table->date('birthdate')->nullable();
            $table->boolean('is_import')->default(false);
            $table->boolean('is_suspended')->default(false);
            $table->string('photo_path')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'jersey_number']);
            $table->index(['tenant_id', 'team_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
