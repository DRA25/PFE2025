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
        Schema::create('attestation_s_f_s', function (Blueprint $table) {
            $table->integer('id_attes')->primary();
            $table->string('libelle_attes');
            $table->integer('debit_attes');
            $table->date('date_attes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attestation_s_f_s');
    }
};
