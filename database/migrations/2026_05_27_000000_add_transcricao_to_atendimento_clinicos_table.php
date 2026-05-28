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
        Schema::table('atendimento_clinicos', function (Blueprint $table) {
            if (!Schema::hasColumn('atendimento_clinicos', 'transcricao')) {
                $table->longText('transcricao')->nullable()->after('hdp');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atendimento_clinicos', function (Blueprint $table) {
            if (Schema::hasColumn('atendimento_clinicos', 'transcricao')) {
                $table->dropColumn('transcricao');
            }
        });
    }
};
