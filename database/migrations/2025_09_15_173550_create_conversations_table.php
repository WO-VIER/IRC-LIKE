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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // Nom pour les groupes, null pour privé
            $table->enum('type', ['private', 'group'])->default('private'); //
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); //Si user supprimé, ses conversations aussi mais peut etre possibilité de "Deleted user"
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            //$table->index(['type', 'last_activity_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
