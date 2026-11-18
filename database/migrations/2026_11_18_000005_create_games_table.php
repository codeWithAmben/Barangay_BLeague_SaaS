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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->foreignId('home_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('best_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->dateTime('scheduled_at')->nullable();
            $table->string('venue')->nullable();
            $table->unsignedSmallInteger('round_number')->nullable();
            $table->string('status')->default('scheduled');
            $table->unsignedSmallInteger('home_score')->default(0);
            $table->unsignedSmallInteger('away_score')->default(0);
            $table->unsignedTinyInteger('current_quarter')->default(1);
            $table->timestamps();

            $table->index(['tenant_id', 'tournament_id', 'status']);
        });

        Schema::create('game_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->boolean('is_starting')->default(false);
            $table->boolean('is_on_court')->default(false);
            $table->unsignedTinyInteger('fouls_count')->default(0);
            $table->boolean('is_ejected')->default(false);
            $table->timestamps();

            $table->unique(['game_id', 'player_id']);
            $table->index(['tenant_id', 'game_id', 'team_id']);
        });

        Schema::create('game_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->unsignedTinyInteger('quarter')->default(1);
            $table->string('game_clock', 10)->nullable();
            $table->string('event_type', 20);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'game_id', 'quarter']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_events');
        Schema::dropIfExists('game_rosters');
        Schema::dropIfExists('games');
    }
};
