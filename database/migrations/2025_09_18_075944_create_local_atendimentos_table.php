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
        Schema::create('local_atendimentos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('endereco', 255)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('email')->unique()->nullable();            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('local_atendimentos');
    }
};
