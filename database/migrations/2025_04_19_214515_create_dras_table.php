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
        Schema::create('dras', function (Blueprint $table) {
            $table->string('n_dra')->primary();
            $table->date('date_creation');
            $table->enum('etat', ['actif', 'cloture'])->default('actif');
            $table->integer('seuil_dra');
            $table->integer('total_dra');
            $table->timestamp('created_at')->useCurrent()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('dras', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
