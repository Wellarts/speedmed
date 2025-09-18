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
            $table->unsignedBigInteger('local_atendimento_id')->nullable()->after('paciente_id');
           
        });
            
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atendimento_clinicos', function (Blueprint $table) {
            $table->dropForeign(['local_atendimento_id']);
            $table->dropColumn('local_atendimento_id');
        });
    }
};
