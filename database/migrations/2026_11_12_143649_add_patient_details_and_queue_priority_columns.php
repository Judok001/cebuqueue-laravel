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
        Schema::table('patients', function (Blueprint $table) {
            $table->text('address')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
        });

        Schema::table('queue_tickets', function (Blueprint $table) {
            $table->string('priority', 20)->default('regular');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_tickets', function (Blueprint $table) {
            $table->dropColumn('priority');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['address', 'age']);
        });
    }
};
