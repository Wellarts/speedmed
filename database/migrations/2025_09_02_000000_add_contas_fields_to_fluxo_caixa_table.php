<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fluxo_caixas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_contas_pagars')->nullable()->after('valor');
            $table->unsignedBigInteger('id_contas_recebers')->nullable()->after('id_contas_pagars');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fluxo_caixas', function (Blueprint $table) {
            $table->dropColumn(['id_contas_pagars', 'id_contas_recebers']);
        });
    }
};
